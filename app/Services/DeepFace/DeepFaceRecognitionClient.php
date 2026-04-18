<?php

namespace App\Services\DeepFace;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepFaceRecognitionClient
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
     * Enroll a face for a subject. Returns DeepFace image_id or null on failure.
     */
    public function addFace(string $subject, string $absoluteImagePath): ?string
    {
        if (! $this->isConfigured() || ! is_readable($absoluteImagePath)) {
            return null;
        }

        try {
            $response = Http::timeout(90)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->attach('file', file_get_contents($absoluteImagePath), basename($absoluteImagePath))
                ->post($this->baseUrl.'/api/v1/recognition/faces?'.http_build_query([
                    'subject' => $subject,
                ]));

            if (! $response->successful()) {
                Log::warning('DeepFace addFace failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $id = $response->json('image_id');

            return is_string($id) ? $id : null;
        } catch (\Throwable $e) {
            Log::warning('DeepFace addFace exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Recognition confidence 0–100 for the expected subject, or null if no face / error.
     */
    public function recognizeSimilarityForSubject(
        string $absoluteImagePath,
        string $expectedSubject,
        int $predictionCount = 8,
    ): ?float {
        if (! $this->isConfigured() || ! is_readable($absoluteImagePath)) {
            return null;
        }

        try {
            $response = Http::timeout(90)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->attach('file', file_get_contents($absoluteImagePath), basename($absoluteImagePath))
                ->post($this->baseUrl.'/api/v1/recognition/recognize?'.http_build_query([
                    'prediction_count' => $predictionCount,
                    'limit' => 1,
                ]));

            if (! $response->successful()) {
                Log::warning('DeepFace recognize failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $results = $response->json('result');
            if (! is_array($results) || $results === []) {
                return null;
            }

            $subjects = $results[0]['subjects'] ?? null;
            if (! is_array($subjects)) {
                return null;
            }

            foreach ($subjects as $row) {
                if (($row['subject'] ?? null) === $expectedSubject) {
                    return round(((float) ($row['similarity'] ?? 0)) * 100, 2);
                }
            }

            return 0.0;
        } catch (\Throwable $e) {
            Log::warning('DeepFace recognize exception', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
