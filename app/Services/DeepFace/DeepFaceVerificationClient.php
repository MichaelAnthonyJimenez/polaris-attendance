<?php

namespace App\Services\DeepFace;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DeepFace "Face verification" application (1:1 match between two images).
 */
class DeepFaceVerificationClient
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
    ) {}

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    /**
     * Compare source_image (e.g. live selfie) to target_image (e.g. ID portrait).
     * Returns similarity 0–1 or null on failure.
     */
    public function verifySimilarity(string $absoluteSourcePath, string $absoluteTargetPath): ?float
    {
        if (! $this->isConfigured() || ! is_readable($absoluteSourcePath) || ! is_readable($absoluteTargetPath)) {
            return null;
        }

        try {
            $response = Http::timeout(90)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->attach('source_image', file_get_contents($absoluteSourcePath), basename($absoluteSourcePath))
                ->attach('target_image', file_get_contents($absoluteTargetPath), basename($absoluteTargetPath))
                ->post($this->baseUrl.'/api/v1/verification/verify');

            if (! $response->successful()) {
                Log::warning('DeepFace verification failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $result = $response->json('result');
            if (! is_array($result) || $result === []) {
                return null;
            }

            $similarity = $result[0]['similarity'] ?? null;

            return is_numeric($similarity) ? (float) $similarity : null;
        } catch (\Throwable $e) {
            Log::warning('DeepFace verification exception', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
