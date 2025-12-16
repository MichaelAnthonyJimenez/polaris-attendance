<?php

namespace App\Services;

use App\Models\DriverFace;

class FaceRecognitionService
{
    public function generateTemplate(string $absoluteImagePath): string
    {
        // Simple deterministic stub that hashes the image contents. Replace with
        // a real embedding extractor (e.g. Rekognition, FaceIO) in production.
        return hash_file('sha1', $absoluteImagePath);
    }

    public function matchLatestForDriver(int $driverId, string $absoluteImagePath): ?float
    {
        $latest = DriverFace::where('driver_id', $driverId)->latest()->first();

        if (! $latest) {
            return null;
        }

        $incomingTemplate = $this->generateTemplate($absoluteImagePath);
        $baseTemplate = $latest->face_template ?? '';

        similar_text($incomingTemplate, (string) $baseTemplate, $percent);

        // Clamp to 2 decimal places for storage.
        return round($percent, 2);
    }
}

