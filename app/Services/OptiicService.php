<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OptiicService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.optiic.api_key') ?? '';
        $this->baseUrl = 'https://api.optiic.dev';
    }

    /**
     * Extract text from image using Optiic.dev OCR API
     */
    public function extractTextFromImage(string $imageData): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'Optiic.dev API key not configured',
                    'text' => '',
                    'words' => [],
                    'avg_confidence' => 0
                ];
            }

            // Remove data URL prefix if present
            $base64Data = $imageData;
            if (str_contains($imageData, 'base64,')) {
                $base64Data = explode('base64,', $imageData)[1];
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->post($this->baseUrl . '/ocr', [
                    'image' => $base64Data,
                    'language' => 'en'
                ]);

            if (!$response->successful()) {
                Log::error('Optiic.dev API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Optiic.dev API error: ' . $response->status(),
                    'text' => '',
                    'words' => [],
                    'avg_confidence' => 0
                ];
            }

            $result = $response->json();

            return [
                'success' => true,
                'text' => $result['text'] ?? '',
                'confidence' => $result['confidence'] ?? 0,
                'words' => $result['words'] ?? [],
                'avg_confidence' => $result['confidence'] ?? 0,
                'processing_time' => $result['processing_time'] ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Optiic.dev service exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'text' => '',
                'words' => [],
                'avg_confidence' => 0
            ];
        }
    }

    /**
     * Check if the API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get API status and information
     */
    public function getApiStatus(): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'API key not configured'
                ];
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->get($this->baseUrl . '/status');

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'API status check failed'
                ];
            }

            return [
                'success' => true,
                'data' => $response->json()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
