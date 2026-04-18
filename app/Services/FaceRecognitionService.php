<?php

namespace App\Services;

use App\Models\DriverFace;
use App\Services\DeepFace\DeepFaceRecognitionClient;

class FaceRecognitionService
{
    public function __construct(
        private DeepFaceRecognitionClient $deepfaceRecognition,
    ) {}

    public function driverSubject(int $driverId): string
    {
        return 'driver-'.$driverId;
    }

    public function deepfaceConfigured(): bool
    {
        return $this->deepfaceRecognition->isConfigured();
    }

    /**
     * Persistable template: DeepFace "df:{image_id}" when enrolled, else legacy SHA1 content hash.
     */
    public function enrollFaceForDriver(int $driverId, string $absoluteImagePath): string
    {
        $client = $this->deepfaceRecognition;
        if ($client->isConfigured()) {
            $imageId = $client->addFace($this->driverSubject($driverId), $absoluteImagePath);
            if ($imageId) {
                return 'df:'.$imageId;
            }
        }

        return hash_file('sha1', $absoluteImagePath);
    }

    /**
     * @deprecated Use enrollFaceForDriver — retains simple hash for callers that do not have a driver id.
     */
    public function generateTemplate(string $absoluteImagePath): string
    {
        return hash_file('sha1', $absoluteImagePath);
    }

    public function matchLatestForDriver(int $driverId, string $absoluteImagePath): ?float
    {
        $latest = DriverFace::where('driver_id', $driverId)->latest()->first();

        if (! $latest) {
            return null;
        }

        $baseTemplate = (string) ($latest->face_template ?? '');

        if (str_starts_with($baseTemplate, 'df:') && $this->deepfaceRecognition->isConfigured()) {
            $match = $this->deepfaceRecognition->recognizeSimilarityForSubject(
                $absoluteImagePath,
                $this->driverSubject($driverId),
            );

            return $match;
        }

        $incomingTemplate = $this->generateTemplate($absoluteImagePath);
        similar_text($incomingTemplate, $baseTemplate, $percent);

        return round($percent, 2);
    }
}
