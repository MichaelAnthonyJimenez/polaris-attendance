<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class IdOcrService
{
    public function extractFromPublicPath(?string $publicDiskPath, ?string $idType = null): array
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
                    return $this->mergeIdContext($local, $idType);
                }
            }

            $compressedPath = $this->createOcrSpaceSizedImage($absolute, $maxOcrSpaceSize);
            if ($compressedPath) {
                try {
                    return $this->mergeIdContext($this->extractWithOcrSpace($compressedPath), $idType);
                } finally {
                    @unlink($compressedPath);
                }
            }

            return ['status' => 'error', 'reason' => 'file_too_large_for_ocr_space', 'message' => 'File too large for OCR.space (1MB limit), and fallback compression/local OCR unavailable'];
        }

        if (in_array($provider, ['easyocr', 'paddleocr'], true)) {
            $local = $this->extractWithLocalEngine($absolute, $provider);
            if (($local['status'] ?? null) === 'ok') {
                return $this->mergeIdContext($local, $idType);
            }
            // Fall back to OCR.Space when local OCR is unavailable.
        }

        return $this->mergeIdContext($this->extractWithOcrSpace($absolute), $idType);
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

    private function extractFields(string $rawText, ?string $idType = null): array
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

        $profile = $this->idTypeProfile($idType);
        if ($profile) {
            $fields['id_type_profile'] = [
                'key' => (string) ($profile['key'] ?? ''),
                'label' => (string) ($profile['label'] ?? ''),
            ];
        }

        $idPatterns = [];
        if (is_array($profile) && isset($profile['id_number_patterns']) && is_array($profile['id_number_patterns'])) {
            $idPatterns = $profile['id_number_patterns'];
        }
        $idPatterns[] = '/(?:id|license|lic|no|number)\s*[:#]?\s*([A-Z0-9-]{5,})/i';
        foreach ($idPatterns as $pattern) {
            if (preg_match($pattern, $normalized, $m)) {
                $fields['id_number'] = trim((string) $m[1]);
                break;
            }
        }

        $namePatterns = [];
        if (is_array($profile) && isset($profile['name_patterns']) && is_array($profile['name_patterns'])) {
            $namePatterns = $profile['name_patterns'];
        }
        $namePatterns[] = '/(?:name|full\s*name|given\s*name|surname|last\s*name|first\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i';
        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $normalized, $m)) {
                $fields['name_line'] = trim((string) $m[1]);
                break;
            }
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
        // Pattern 1: Look for "LASTNAME, FIRSTNAME MIDDLEINITIAL" pattern
        foreach ($lines as $line) {
            $upperLine = strtoupper($line);
            if (
                preg_match('/^[A-Z][A-Z .,\']{4,}$/', $upperLine)
                && ! preg_match('/\b(?:republic|philippines|address|birth|sex|nationality|id|license|number|college|university|center|school|campus|city|municipality|province|region|government|office|department)\b/i', $line)
                && preg_match('/\b[A-Z]{2,}\s*,\s*[A-Z]{2,}(?:\s+[A-Z]\.?)?\b/', $upperLine) // Look for "LASTNAME, FIRSTNAME" or "LASTNAME, FIRSTNAME M."
            ) {
                $nameCandidates[] = trim($line);
            }
        }
        // Pattern 2: Extract names from lines with comma-separated names
        foreach ($lines as $line) {
            if (
                preg_match('/([A-Z][A-Z .,\']{2,})\s*,\s*([A-Z][A-Z .,\']{2,})(?:\s+([A-Z]\.))?/', $line, $matches)
                && ! preg_match('/\b(?:college|university|center|school|campus|city|municipality|province|region|government|office|department)\b/i', $line)
            ) {
                $fullName = trim($matches[1] . ', ' . $matches[2] . (isset($matches[3]) ? ' ' . $matches[3] : ''));
                if (! in_array($fullName, $nameCandidates)) {
                    $nameCandidates[] = $fullName;
                }
            }
        }
        // Pattern 3: Look for lines that start with common name indicators
        foreach ($lines as $line) {
            if (
                preg_match('/^(?:name|full\s*name|given\s*name|surname|last\s*name|first\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i', $line, $matches)
                && ! preg_match('/\b(?:college|university|center|school|campus|city|municipality|province|region|government|office|department)\b/i', $matches[1])
            ) {
                $nameCandidates[] = trim($matches[1]);
            }
        }
        // Pattern 4: Look for lines that contain name-like patterns (all caps with proper name structure)
        foreach ($lines as $line) {
            $upperLine = strtoupper($line);
            if (
                preg_match('/^[A-Z][A-Z ]{4,}$/', $upperLine) // All caps line
                && ! preg_match('/\b(?:republic|philippines|address|birth|sex|nationality|id|license|number|college|university|center|school|campus|city|municipality|province|region|government|office|department|street|road|barangay|district)\b/i', $line)
                && preg_match('/\b[A-Z]{2,}\s+[A-Z]{2,}\b/', $upperLine) // At least two words
                && strlen($line) >= 8 // Minimum length for a name
            ) {
                // Check if it looks like a person's name (not too long, reasonable word count)
                $words = array_filter(explode(' ', $line));
                if (count($words) >= 2 && count($words) <= 4) {
                    $nameCandidates[] = trim($line);
                }
            }
        }
        if ($nameCandidates !== []) {
            $fields['name_candidates'] = array_values(array_unique($nameCandidates));
        }

        $fields['raw_text'] = $rawText;

        return $fields;
    }

    private function mergeIdContext(array $ocrResult, ?string $idType): array
    {
        if (($ocrResult['status'] ?? null) !== 'ok') {
            return $ocrResult;
        }

        $rawText = trim((string) ($ocrResult['raw_text'] ?? ''));
        $fields = $this->extractFields($rawText, $idType);
        $ocrResult['fields'] = $fields;
        if ($idType) {
            $ocrResult['id_type'] = $idType;
        }

        return $ocrResult;
    }

    private function idTypeProfile(?string $idType): ?array
    {
        $key = strtolower(trim((string) $idType));
        if ($key === '' || $key === 'other' || $key === 'ocr_auto_detect') {
            return null;
        }

        $profiles = [
            'philsys_national_id' => [
                'label' => 'PhilSys National ID',
                'id_number_patterns' => [
                    '/(?:pcn|philippine\s*identification\s*number|philsys\s*number)\s*[:#]?\s*([A-Z0-9-]{6,})/i',
                    '/\b(\d{4}-\d{7}-\d{1})\b/', // PhilSys format: XXXX-XXXXXXX-X
                ],
                'name_patterns' => [
                    '/(?:name|full\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'drivers_license' => [
                'label' => "Driver's License",
                'id_number_patterns' => [
                    '/(?:license\s*no\.?|lic\.?\s*no\.?|driver\s*license\s*no)\s*[:#]?\s*([A-Z0-9-]{5,})/i',
                    '/\b([A-Z]\d{2}-\d{2}-\d{6})\b/', // License format
                ],
                'name_patterns' => [
                    '/(?:name|driver\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'passport' => [
                'label' => 'Passport',
                'id_number_patterns' => [
                    '/(?:passport\s*no\.?|passport\s*number)\s*[:#]?\s*([A-Z0-9-]{6,})/i',
                    '/\b([A-Z]{1,2}\d{7})\b/', // Passport format
                ],
                'name_patterns' => [
                    '/(?:name|surname|given\s*names)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'student_id' => [
                'label' => 'Student ID',
                'id_number_patterns' => [
                    '/(?:student\s*id|id\s*no\.?|student\s*no\.?|control\s*no)\s*[:#]?\s*([A-Z0-9-]{5,})/i',
                    '/\b(\d{6,12})\b/',
                ],
                'name_patterns' => [
                    '/(?:name|student\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'umid' => [
                'label' => 'UMID',
                'id_number_patterns' => [
                    '/(?:crn|umid|sss|crn\s*no)\s*[:#]?\s*([A-Z0-9-]{6,})/i',
                    '/\b(\d{4}-\d{7}-\d{1})\b/', // UMID format
                ],
                'name_patterns' => [
                    '/(?:name|member\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'prc_id' => [
                'label' => 'PRC ID',
                'id_number_patterns' => [
                    '/(?:registration\s*no\.?|prc\s*no\.?|license\s*no\.?)\s*[:#]?\s*([A-Z0-9-]{5,})/i',
                    '/\b(\d{3}-\d{7}-\d{1})\b/', // PRC format
                ],
                'name_patterns' => [
                    '/(?:name|professional\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'postal_id' => [
                'label' => 'Postal ID',
                'id_number_patterns' => [
                    '/(?:postal\s*id|id\s*no)\s*[:#]?\s*([A-Z0-9-]{5,})/i',
                ],
                'name_patterns' => [
                    '/(?:name|postal\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'voters_id' => [
                'label' => "Voter's ID",
                'id_number_patterns' => [
                    '/(?:voter|vin|voter\s*id|voter\s*no)\s*[:#]?\s*([A-Z0-9-]{6,})/i',
                ],
                'name_patterns' => [
                    '/(?:name|voter\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'philhealth_id' => [
                'label' => 'PhilHealth ID',
                'id_number_patterns' => [
                    '/(?:philhealth|pin|philhealth\s*id|philhealth\s*no)\s*[:#]?\s*([A-Z0-9-]{6,})/i',
                    '/\b(\d{2}-\d{9})\b/', // PhilHealth format
                ],
                'name_patterns' => [
                    '/(?:name|member\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'sss_id' => [
                'label' => 'SSS ID',
                'id_number_patterns' => [
                    '/(?:sss\s*no\.?|sss\s*id|social\s*security)\s*[:#]?\s*([A-Z0-9-]{6,})/i',
                    '/\b(\d{2}-\d{7}-\d{1})\b/', // SSS format
                ],
                'name_patterns' => [
                    '/(?:name|member\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'pagibig_loyalty_card' => [
                'label' => 'Pag-IBIG Loyalty Card',
                'id_number_patterns' => [
                    '/(?:pag-?ibig|hdlmf|pagibig\s*id|pagibig\s*no)\s*[:#]?\s*([A-Z0-9-]{6,})/i',
                    '/\b(\d{4}-\d{6}-\d{1})\b/', // Pag-IBIG format
                ],
                'name_patterns' => [
                    '/(?:name|member\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'senior_citizen_id' => [
                'label' => 'Senior Citizen ID',
                'id_number_patterns' => [
                    '/(?:senior\s*citizen|senior\s*id|osca\s*id)\s*[:#]?\s*([A-Z0-9-]{5,})/i',
                ],
                'name_patterns' => [
                    '/(?:name|senior\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'ofw_id' => [
                'label' => 'OFW ID',
                'id_number_patterns' => [
                    '/(?:ofw|ofw\s*id|ofw\s*no)\s*[:#]?\s*([A-Z0-9-]{5,})/i',
                ],
                'name_patterns' => [
                    '/(?:name|ofw\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
            'barangay_id' => [
                'label' => 'Barangay ID',
                'id_number_patterns' => [
                    '/(?:barangay|barangay\s*id|barangay\s*no)\s*[:#]?\s*([A-Z0-9-]{5,})/i',
                ],
                'name_patterns' => [
                    '/(?:name|resident\s*name)\s*[:#]?\s*([A-Z][A-Z .,\']{2,})/i',
                ],
            ],
        ];

        if (! isset($profiles[$key])) {
            return null;
        }

        return array_merge(['key' => $key], $profiles[$key]);
    }
}

