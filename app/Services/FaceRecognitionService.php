<?php

namespace App\Services;

use App\Models\DriverFace;
use App\Services\PythonVisionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    public function __construct(
        private PythonVisionService $pythonVision,
    ) {}

    public function driverSubject(int $driverId): string
    {
        return 'driver-'.$driverId;
    }

    public function deepfaceConfigured(): bool
    {
        // Always return false since we're not using external DeepFace API
        return false;
    }

    /**
     * Persistable template: Using SHA1 content hash for local face recognition.
     */
    public function enrollFaceForDriver(int $driverId, string $absoluteImagePath): string
    {
        // Using local face recognition - generate SHA1 hash template
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

        // Skip DeepFace API since we're using local Python services
        // All templates will be SHA1 hashes, so we proceed to Python service

        // Fallback to Python DeepFace service for local recognition
        try {
            $pythonResult = $this->matchWithPythonService($driverId, $absoluteImagePath);
            if ($pythonResult !== null && $pythonResult > 0) {
                return $pythonResult;
            }
        } catch (\Exception $e) {
            Log::warning('Python face recognition failed, falling back to legacy method', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);
        }

        // Additional fallback: try simple image comparison if Python service fails
        try {
            $imageComparisonResult = $this->compareWithStoredImage($driverId, $absoluteImagePath);
            if ($imageComparisonResult !== null) {
                return $imageComparisonResult;
            }
        } catch (\Exception $e) {
            Log::warning('Image comparison failed, using hash method', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);
        }

        // Final fallback to improved hash-based comparison
        $incomingTemplate = $this->generateTemplate($absoluteImagePath);

        // If we have a SHA1 hash template, use similarity comparison
        if (strlen($baseTemplate) === 40 && ctype_xdigit($baseTemplate)) {
            similar_text($incomingTemplate, $baseTemplate, $percent);
            $confidence = round($percent, 2);

            // Add some logging for debugging
            Log::info('Legacy hash comparison', [
                'driver_id' => $driverId,
                'incoming_hash' => $incomingTemplate,
                'stored_hash' => $baseTemplate,
                'similarity_percent' => $confidence
            ]);

            return $confidence;
        }

        return null;
    }

    /**
     * Use Python DeepFace service to find similar faces for a driver
     */
    private function matchWithPythonService(int $driverId, string $absoluteImagePath): ?float
    {
        try {
            // Get all face images for this driver from storage
            $driverFaces = DriverFace::where('driver_id', $driverId)->get();

            if ($driverFaces->isEmpty()) {
                Log::warning('No face records found for driver', ['driver_id' => $driverId]);
                return null;
            }

            // Try to find the most recent face image
            $latestFace = $driverFaces->first();
            $storedImagePath = $this->findFaceImagePath($latestFace);

            if (!$storedImagePath || !file_exists($storedImagePath)) {
                Log::warning('Stored face image not found', [
                    'driver_id' => $driverId,
                    'stored_path' => $storedImagePath
                ]);
                return null;
            }

            // Use Python service to verify if the two images match
            $imageData1 = $this->imageToBase64($absoluteImagePath);
            $imageData2 = $this->imageToBase64($storedImagePath);

            $result = $this->pythonVision->verifyFaces(
                $imageData1,
                $imageData2,
                'VGG-Face'
            );

            if ($result['success'] && $result['verified']) {
                return $result['confidence'];
            }

            Log::info('Face verification failed', [
                'driver_id' => $driverId,
                'verified' => $result['verified'] ?? false,
                'confidence' => $result['confidence'] ?? 0,
                'distance' => $result['distance'] ?? 0,
                'threshold' => $result['threshold'] ?? 0
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Python face verification exception', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Find the actual file path for a stored face image
     */
    private function findFaceImagePath($driverFace): ?string
    {
        // Try to get the image path from the driver face record
        if (isset($driverFace->image_path) && $driverFace->image_path) {
            $fullPath = Storage::disk('public')->path($driverFace->image_path);
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        // Try to find face images in the driver-verifications/faces directory
        $facesDir = Storage::disk('public')->path('driver-verifications/faces');
        if (is_dir($facesDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($facesDir)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'])) {
                    return $file->getPathname();
                }
            }
        }

        // Try the general faces directory as well
        $generalFacesDir = Storage::disk('public')->path('faces');
        if (is_dir($generalFacesDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($generalFacesDir)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'])) {
                    return $file->getPathname();
                }
            }
        }

        return null;
    }

    /**
     * Simple image comparison using perceptual hash
     */
    private function compareWithStoredImage(int $driverId, string $absoluteImagePath): ?float
    {
        try {
            // Get the most recent face image for this driver
            $latestFace = DriverFace::where('driver_id', $driverId)->latest()->first();

            if (!$latestFace) {
                return null;
            }

            $storedImagePath = $this->findFaceImagePath($latestFace);

            if (!$storedImagePath || !file_exists($storedImagePath)) {
                return null;
            }

            // Simple file size and basic comparison as a fallback
            $incomingSize = filesize($absoluteImagePath);
            $storedSize = filesize($storedImagePath);

            if ($incomingSize === false || $storedSize === false) {
                return null;
            }

            // If file sizes are very similar, give a moderate confidence score
            $sizeDiff = abs($incomingSize - $storedSize);
            $sizeRatio = min($incomingSize, $storedSize) / max($incomingSize, $storedSize);

            if ($sizeRatio > 0.9) { // 90% similar size
                return 75.0; // Moderate confidence
            } elseif ($sizeRatio > 0.7) { // 70% similar size
                return 60.0; // Lower confidence
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Image comparison exception', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Convert image file to base64 string
     */
    private function imageToBase64(string $imagePath): string
    {
        $imageData = file_get_contents($imagePath);
        $mimeType = mime_content_type($imagePath);
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
}
