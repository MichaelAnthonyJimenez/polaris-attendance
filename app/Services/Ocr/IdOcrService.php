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

        // If file is larger than OCR.space limit, force local OCR
        if ($fileSize > $maxOcrSpaceSize) {
            $localProviders = ['easyocr', 'paddleocr'];
            foreach ($localProviders as $localProvider) {
                $local = $this->extractWithLocalEngine($absolute, $localProvider);
                if (($local['status'] ?? null) === 'ok') {
                    return $local;
                }
            }
            return ['status' => 'error', 'reason' => 'file_too_large_for_ocr_space', 'message' => 'File too large for OCR.space (1MB limit) and local OCR unavailable'];
        }

        if (in_array($provider, ['easyocr', 'paddleocr'], true)) {
            $local = $this->extractWithLocalEngine($absolute, $provider);
            if (($local['status'] ?? null) === 'ok') {
                return $local;
            }
            // Fall back to OCR.Space when local OCR is unavailable.
        }

        $apiKey = trim((string) config('services.ocr_space.api_key', ''));
        if ($apiKey === '') {
            $apiKey = 'helloworld';
        }

        try {
            $response = Http::asMultipart()
                ->timeout(20)
                ->attach('file', file_get_contents($absolute), basename($absolute))
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

    private function extractFields(string $rawText): array
    {
        $fields = [];
        if (preg_match('/(?:ID|LIC|NO|NUMBER)\s*[:#]?\s*([A-Z0-9-]{5,})/i', $rawText, $m)) {
            $fields['id_number'] = trim($m[1]);
        }
        if (preg_match('/(?:NAME)\s*[:#]?\s*([A-Z .,\']{4,})/i', $rawText, $m)) {
            $fields['name_line'] = trim($m[1]);
        }

        return $fields;
    }
}

