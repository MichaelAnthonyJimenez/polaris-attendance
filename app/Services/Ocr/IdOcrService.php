<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class IdOcrService
{
    public function extractFromPublicPath(?string $publicDiskPath): array
    {
        if (! $publicDiskPath) {
            return ['status' => 'skipped', 'reason' => 'missing_image'];
        }

        $absolute = Storage::disk('public')->path($publicDiskPath);
        if (! is_file($absolute)) {
            return ['status' => 'skipped', 'reason' => 'file_not_found'];
        }

        // Check file size - OCR.space has 1MB limit, use local OCR for larger files
        $fileSize = filesize($absolute);
        $maxOcrSpaceSize = 1 * 1024 * 1024; // 1MB for OCR.space
        $maxLocalSize = 10 * 1024 * 1024; // 10MB for local OCR

        if ($fileSize > $maxLocalSize) {
            return ['status' => 'error', 'reason' => 'file_too_large', 'message' => 'File size exceeds maximum limit of 10MB'];
        }

        $provider = strtolower(trim((string) config('services.ocr_space.provider', 'ocr_space')));

        // If file is larger than OCR.space limit, try local OCR first.
        // If local OCR is unavailable, compress image and retry OCR.space.
        if ($fileSize > $maxOcrSpaceSize) {
            $localProviders = ['easyocr', 'paddleocr'];
            foreach ($localProviders as $localProvider) {
                $local = $this->extractWithLocalEngine($absolute, $localProvider);
                if (($local['status'] ?? null) === 'ok') {
                    return $local;
                }
            }

            $compressedPath = $this->createOcrSpaceSizedImage($absolute, $maxOcrSpaceSize);
            if ($compressedPath) {
                try {
                    return $this->extractWithOcrSpace($compressedPath);
                } finally {
                    @unlink($compressedPath);
                }
            }

            return ['status' => 'error', 'reason' => 'file_too_large_for_ocr_space', 'message' => 'File too large for OCR.space (1MB limit), and fallback compression/local OCR unavailable'];
        }

        if (in_array($provider, ['easyocr', 'paddleocr'], true)) {
            $local = $this->extractWithLocalEngine($absolute, $provider);
            if (($local['status'] ?? null) === 'ok') {
                return $local;
            }
            // Fall back to OCR.Space when local OCR is unavailable.
        }

        return $this->extractWithOcrSpace($absolute);
    }

    private function extractWithLocalEngine(string $absolutePath, string $engine): array
    {
        $python = trim((string) config('services.ocr_space.python_bin', 'python'));
        $script = trim((string) config('services.ocr_space.script_path', base_path('scripts/ocr_local.py')));
        if ($script === '' || ! is_file($script)) {
            return ['status' => 'error', 'reason' => 'local_ocr_script_missing'];
        }

        $lang = (string) config('services.ocr_space.local_language', 'en');
        $process = new Process([$python, $script, '--image', $absolutePath, '--engine', $engine, '--lang', $lang]);
        $process->setTimeout(60);

        try {
            $process->run();
        } catch (\Throwable $e) {
            report($e);

            return ['status' => 'error', 'reason' => 'local_ocr_process_failed'];
        }

        $output = trim((string) $process->getOutput());
        $payload = json_decode($output, true);
        if (! is_array($payload)) {
            return ['status' => 'error', 'reason' => 'local_ocr_invalid_output'];
        }

        if (($payload['status'] ?? null) !== 'ok') {
            return [
                'status' => 'error',
                'reason' => (string) ($payload['reason'] ?? 'local_ocr_error'),
                'message' => (string) ($payload['message'] ?? ''),
            ];
        }

        $rawText = trim((string) ($payload['raw_text'] ?? ''));

        return [
            'status' => 'ok',
            'raw_text' => $rawText,
            'fields' => $this->extractFields($rawText),
            'provider' => $engine,
        ];
    }

    private function extractWithOcrSpace(string $absolutePath): array
    {
        $apiKey = trim((string) config('services.ocr_space.api_key', ''));
        if ($apiKey === '') {
            $apiKey = 'helloworld';
        }

        try {
            $response = Http::asMultipart()
                ->timeout(20)
                ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
                ->post((string) config('services.ocr_space.endpoint', 'https://api.ocr.space/parse/image'), [
                    'apikey' => $apiKey,
                    'language' => (string) config('services.ocr_space.language', 'eng'),
                    'isOverlayRequired' => 'false',
                    'scale' => 'true',
                ]);
        } catch (\Throwable $e) {
            report($e);

            return ['status' => 'error', 'reason' => 'request_failed'];
        }

        if (! $response->ok()) {
            return ['status' => 'error', 'reason' => 'http_' . $response->status()];
        }

        $payload = $response->json();
        if ((bool) data_get($payload, 'IsErroredOnProcessing', false) === true) {
            $errorMessage = (string) data_get($payload, 'ErrorMessage.0', data_get($payload, 'ErrorMessage', 'processing_error'));

            return ['status' => 'error', 'reason' => 'ocr_processing_error', 'message' => $errorMessage];
        }

        $parsed = data_get($payload, 'ParsedResults.0.ParsedText', '');
        $rawText = trim((string) $parsed);

        if ($rawText === '') {
            return ['status' => 'ok', 'raw_text' => '', 'fields' => []];
        }

        return [
            'status' => 'ok',
            'raw_text' => $rawText,
            'fields' => $this->extractFields($rawText),
            'provider' => 'ocr_space',
        ];
    }

    private function createOcrSpaceSizedImage(string $absolutePath, int $maxBytes): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $imageInfo = @getimagesize($absolutePath);
        if (! is_array($imageInfo)) {
            return null;
        }

        $mime = strtolower((string) ($imageInfo['mime'] ?? ''));
        $src = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => @imagecreatefrompng($absolutePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false,
        };

        if (! $src) {
            return null;
        }

        $srcWidth = imagesx($src);
        $srcHeight = imagesy($src);
        if ($srcWidth <= 0 || $srcHeight <= 0) {
            imagedestroy($src);

            return null;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'ocr_');
        if ($tmpPath === false) {
            imagedestroy($src);

            return null;
        }

        $targetPath = $tmpPath . '.jpg';
        @unlink($tmpPath);

        $scales = [1.0, 0.9, 0.8, 0.7, 0.6];
        foreach ($scales as $scale) {
            $dstWidth = max(1, (int) round($srcWidth * $scale));
            $dstHeight = max(1, (int) round($srcHeight * $scale));
            $dst = imagecreatetruecolor($dstWidth, $dstHeight);
            if (! $dst) {
                continue;
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);

            foreach ([80, 70, 60, 50, 40, 30] as $quality) {
                if (@imagejpeg($dst, $targetPath, $quality) && @filesize($targetPath) <= $maxBytes) {
                    imagedestroy($dst);
                    imagedestroy($src);

                    return $targetPath;
                }
            }

            imagedestroy($dst);
        }

        imagedestroy($src);
        @unlink($targetPath);

        return null;
    }

    private function extractFields(string $rawText): array
    {
        $fields = [];
        $normalized = preg_replace('/\r\n?/', "\n", trim($rawText)) ?? '';
        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim(preg_replace('/\s+/', ' ', $line) ?? ''),
            explode("\n", $normalized)
        )));

        if ($lines !== []) {
            $fields['all_text_lines'] = $lines;
        }

        $keyValues = [];
        foreach ($lines as $line) {
            if (preg_match('/^\s*([A-Z][A-Z0-9 .\/_-]{1,40})\s*[:#]\s*(.{1,120})\s*$/i', $line, $m)) {
                $label = strtolower(trim((string) $m[1]));
                $label = preg_replace('/[^a-z0-9]+/', '_', $label) ?? $label;
                $label = trim($label, '_');
                if ($label !== '' && ! isset($keyValues[$label])) {
                    $keyValues[$label] = trim((string) $m[2]);
                }
            }
        }
        if ($keyValues !== []) {
            $fields['key_values'] = $keyValues;
        }

        if (preg_match('/(?:id|license|lic|no|number)\s*[:#]?\s*([A-Z0-9-]{5,})/i', $normalized, $m)) {
            $fields['id_number'] = trim((string) $m[1]);
        }

        if (preg_match('/(?:name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i', $normalized, $m)) {
            $fields['name_line'] = trim((string) $m[1]);
        }

        if (preg_match_all('/\b(?:\d{4}[-\/]\d{2}[-\/]\d{2}|\d{2}[-\/]\d{2}[-\/]\d{4}|\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4})\b/', $normalized, $m) && ! empty($m[0])) {
            $fields['date_values'] = array_values(array_unique(array_map('trim', $m[0])));
        }

        if (preg_match_all('/\b[A-Z0-9]{6,20}\b/', strtoupper($normalized), $m) && ! empty($m[0])) {
            $tokens = array_values(array_unique($m[0]));
            if ($tokens !== []) {
                $fields['alphanumeric_tokens'] = array_slice($tokens, 0, 25);
            }
        }

        $nameCandidates = [];
        foreach ($lines as $line) {
            if (
                preg_match('/^[A-Z][A-Z .,\']{4,}$/', strtoupper($line))
                && ! preg_match('/\b(?:republic|philippines|address|birth|sex|nationality|id|license|number)\b/i', $line)
            ) {
                $nameCandidates[] = trim($line);
            }
        }
        if ($nameCandidates !== []) {
            $fields['name_candidates'] = array_values(array_unique($nameCandidates));
        }

        $fields['raw_text'] = $rawText;

        return $fields;
    }
}

