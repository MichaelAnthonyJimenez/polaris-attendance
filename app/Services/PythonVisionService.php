<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PythonVisionService
{
    private string $pythonPath;
    private string $scriptPath;

    public function __construct()
    {
        $this->pythonPath = 'python'; // Assumes python is in PATH
        $this->scriptPath = base_path('python');
    }

    /**
     * Extract text from an image using OCR
     */
    public function extractTextFromImage(string $imageData): array
    {
        try {
            // Try Paddle OCR service first (primary)
            $result = $this->runPythonScript('ocr_service.py', [$imageData]);
            $parsed = json_decode($result, true);

            if ($parsed && isset($parsed['success'])) {
                Log::info('Paddle OCR successful', [
                    'confidence' => $parsed['avg_confidence'],
                    'text_length' => strlen($parsed['text'])
                ]);
                return $parsed;
            }

            // Fallback to simple OCR service
            $result = $this->runPythonScript('simple_ocr_service.py', [$imageData]);
            $parsed = json_decode($result, true);

            if ($parsed && isset($parsed['success'])) {
                Log::info('Simple OCR successful', [
                    'confidence' => $parsed['avg_confidence'],
                    'text_length' => strlen($parsed['text'])
                ]);
                return $parsed;
            }

            Log::error('All OCR services failed');
            return [
                'success' => false,
                'error' => 'All OCR services failed',
                'text' => '',
                'words' => [],
                'avg_confidence' => 0
            ];
        } catch (\Exception $e) {
            Log::error('OCR extraction error: ' . $e->getMessage());
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
     * Detect faces in an image
     */
    public function detectFaces(string $imageData, bool $enforceDetection = true): array
    {
        try {
            $args = [$imageData];
            if ($enforceDetection) {
                $args[] = '--enforce-detection';
            }

            $result = $this->runPythonScript('deepface_service.py', array_merge(['detect'], $args));
            return json_decode($result, true) ?: [
                'success' => false,
                'error' => 'Failed to parse face detection response',
                'faces' => [],
                'total_faces' => 0
            ];
        } catch (\Exception $e) {
            Log::error('Face Detection Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'faces' => [],
                'total_faces' => 0
            ];
        }
    }

    /**
     * Verify if two images contain the same person
     */
    public function verifyFaces(string $image1Data, string $image2Data, string $model = 'VGG-Face'): array
    {
        try {
            $result = $this->runPythonScript('deepface_service.py', [
                'verify',
                $image1Data,
                $image2Data,
                '--model',
                $model
            ]);

            return json_decode($result, true) ?: [
                'success' => false,
                'error' => 'Failed to parse face verification response',
                'verified' => false,
                'distance' => 0,
                'threshold' => 0,
                'model' => $model,
                'confidence' => 0
            ];
        } catch (\Exception $e) {
            Log::error('Face Verification Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'verified' => false,
                'distance' => 0,
                'threshold' => 0,
                'model' => $model,
                'confidence' => 0
            ];
        }
    }

    /**
     * Analyze facial attributes (age, gender, emotion, race)
     */
    public function analyzeFace(string $imageData, array $actions = ['age', 'gender', 'emotion', 'race']): array
    {
        try {
            $args = array_merge(['analyze', $imageData], array_map(fn($action) => "--action=$action", $actions));
            $result = $this->runPythonScript('deepface_service.py', $args);

            return json_decode($result, true) ?: [
                'success' => false,
                'error' => 'Failed to parse face analysis response',
                'analysis' => [],
                'actions_performed' => $actions
            ];
        } catch (\Exception $e) {
            Log::error('Face Analysis Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'analysis' => [],
                'actions_performed' => $actions
            ];
        }
    }

    /**
     * Enroll a face in the database
     */
    public function enrollFace(string $imageData, string $personId, string $dbPath): array
    {
        try {
            $result = $this->runPythonScript('deepface_service.py', [
                'enroll',
                $imageData,
                $personId,
                '--db-path',
                $dbPath
            ]);

            return json_decode($result, true) ?: [
                'success' => false,
                'error' => 'Failed to parse face enrollment response',
                'filepath' => null,
                'person_id' => $personId
            ];
        } catch (\Exception $e) {
            Log::error('Face Enrollment Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'filepath' => null,
                'person_id' => $personId
            ];
        }
    }

    /**
     * Find similar faces in a database
     */
    public function findSimilarFaces(string $imageData, string $dbPath, string $model = 'VGG-Face', float $threshold = 0.4): array
    {
        try {
            $result = $this->runPythonScript('deepface_service.py', [
                'find-similar',
                $imageData,
                '--db-path',
                $dbPath,
                '--model',
                $model,
                '--threshold',
                (string)$threshold
            ]);

            return json_decode($result, true) ?: [
                'success' => false,
                'error' => 'Failed to parse face search response',
                'matches' => [],
                'total_matches' => 0
            ];
        } catch (\Exception $e) {
            Log::error('Face Search Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'matches' => [],
                'total_matches' => 0
            ];
        }
    }

    /**
     * Run a Python script and return the output
     */
    private function runPythonScript(string $script, array $args = []): string
    {
        $scriptPath = $this->scriptPath . '/' . $script;

        if (!file_exists($scriptPath)) {
            throw new \Exception("Python script not found: $scriptPath");
        }

        // Build command with escaped arguments
        $command = array_merge([$this->pythonPath, $scriptPath], $args);

        // Run the process
        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            $output = $process->getOutput();
            throw new \Exception("Python script failed: $errorOutput\nOutput: $output");
        }

        return $process->getOutput();
    }

    /**
     * Check if Python services are available
     */
    public function isAvailable(): bool
    {
        try {
            $process = new Process([$this->pythonPath, '--version']);
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Python not available: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if specific Python packages are installed
     */
    public function checkDependencies(): array
    {
        $packages = ['deepface', 'opencv-python', 'paddleocr', 'paddlepaddle', 'pillow'];
        $results = [];

        foreach ($packages as $package) {
            try {
                $process = new Process([
                    $this->pythonPath,
                    '-c',
                    "import {$package}; print('OK')"
                ]);
                $process->run();
                $results[$package] = $process->isSuccessful() && str_contains($process->getOutput(), 'OK');
            } catch (\Exception $e) {
                $results[$package] = false;
            }
        }

        return $results;
    }

    /**
     * Create face database directory structure
     */
    public function createFaceDatabase(string $dbPath): bool
    {
        try {
            if (!is_dir($dbPath)) {
                mkdir($dbPath, 0755, true);
            }
            return is_dir($dbPath);
        } catch (\Exception $e) {
            Log::error('Failed to create face database', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
