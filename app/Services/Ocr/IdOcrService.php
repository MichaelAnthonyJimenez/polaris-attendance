<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

        $apiKey = (string) config('services.ocr_space.api_key', '');
        if ($apiKey === '') {
            return ['status' => 'skipped', 'reason' => 'ocr_not_configured'];
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

