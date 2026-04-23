<?php

namespace App\Http\Controllers;

use App\Models\DriverFace;
use App\Models\DriverVerification;
use App\Models\User;
use App\Services\FaceRecognitionService;
use App\Services\LivenessService;
use App\Services\Ocr\IdOcrService;
use App\Services\PythonVisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriverVerificationSubmissionController extends Controller
{
    public function __construct(
        private FaceRecognitionService $faceService,
        private LivenessService $livenessService,
        private IdOcrService $idOcrService,
        private PythonVisionService $pythonVision,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $role = strtolower((string) ($user->role ?? ''));

        $data = $request->validate([
            'verification_method' => ['required', 'in:facial,id_only'],
            'proof_mode' => ['nullable', 'in:selfie_with_id,upload_file'],
            'id_type' => ['nullable', 'string', 'max:50'],

            'face_front_base64' => ['required_if:verification_method,facial', 'nullable', 'string'],
            'face_left_base64' => ['required_if:verification_method,facial', 'nullable', 'string'],
            'face_right_base64' => ['required_if:verification_method,facial', 'nullable', 'string'],

            'id_front_base64' => ['nullable', 'string'],
            'id_back_base64' => ['nullable', 'string'],
            'face_selfie_base64' => ['nullable', 'string'],
            'id_front_file' => ['nullable', 'image', 'max:5120'],
            'id_back_file' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($role !== 'driver') {
            // Still allow submission; admin can review.
        }

        $driver = User::query()->where('id', $user->id)->where('role', 'driver')->first();
        if (! $driver) {
            abort(403, 'Only driver accounts can submit verification.');
        }

        $verification = new DriverVerification();
        $verification->user_id = $user->id;
        $verification->driver_id = $driver->id;
        $verification->verification_method = $data['verification_method'];
        $verification->status = 'pending';

        $meta = [];

        if ($data['verification_method'] === 'facial' && ! empty($data['face_front_base64'])) {
            $frontPath = $this->storeBase64Image($data['face_front_base64'], 'verification/facial/'.$driver->id);
            $leftPath = ! empty($data['face_left_base64'])
                ? $this->storeBase64Image($data['face_left_base64'], 'verification/facial/'.$driver->id)
                : null;
            $rightPath = ! empty($data['face_right_base64'])
                ? $this->storeBase64Image($data['face_right_base64'], 'verification/facial/'.$driver->id)
                : null;

            if ($frontPath) {
                $verification->face_image_path = $frontPath;
                $meta['face_sequence'] = array_filter([
                    'front' => $frontPath,
                    'left' => $leftPath,
                    'right' => $rightPath,
                ]);

                $fullPath = Storage::disk('public')->path($frontPath);
                $meta['liveness_score'] = $this->livenessService->score($fullPath);

                // Enhanced face detection using Python DeepFace service
                $pythonFaceResult = null;
                if ($this->pythonVision->isAvailable()) {
                    try {
                        $imageData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($fullPath));
                        $detectionResult = $this->pythonVision->detectFaces($imageData, true);

                        if ($detectionResult['success']) {
                            $meta['python_face_detection'] = [
                                'total_faces' => $detectionResult['total_faces'],
                                'faces_detected' => $detectionResult['faces'],
                                'detection_success' => true
                            ];

                            // Analyze face attributes
                            $analysisResult = $this->pythonVision->analyzeFace($imageData, ['age', 'gender', 'emotion']);
                            if ($analysisResult['success']) {
                                $meta['python_face_analysis'] = $analysisResult['analysis'];
                            }
                        }
                    } catch (\Exception $e) {
                        $meta['python_face_detection'] = ['detection_success' => false, 'error' => $e->getMessage()];
                    }
                }

                $hasExistingFace = DriverFace::where('driver_id', $driver->id)->exists();

                if ($hasExistingFace) {
                    // Try Python service first for face verification
                    $similarity = null;
                    if ($this->pythonVision->isAvailable() && isset($meta['python_face_detection']['detection_success']) && $meta['python_face_detection']['detection_success']) {
                        try {
                            $latestFace = DriverFace::where('driver_id', $driver->id)->latest()->first();
                            if ($latestFace && $latestFace->image_path && file_exists(storage_path('app/public/' . $latestFace->image_path))) {
                                $storedFaceData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $latestFace->image_path)));
                                $verificationResult = $this->pythonVision->verifyFaces($imageData, $storedFaceData, 'VGG-Face');

                                if ($verificationResult['success']) {
                                    $similarity = $verificationResult['confidence'];
                                    $meta['python_face_verification'] = [
                                        'verified' => $verificationResult['verified'],
                                        'confidence' => $verificationResult['confidence'],
                                        'distance' => $verificationResult['distance'],
                                        'model' => $verificationResult['model']
                                    ];
                                }
                            }
                        } catch (\Exception $e) {
                            $meta['python_face_verification'] = ['error' => $e->getMessage()];
                        }
                    }

                    // Fallback to original method if Python service failed
                    if ($similarity === null) {
                        $similarity = $this->faceService->matchLatestForDriver($driver->id, $fullPath);
                    }

                    $meta['deepface_recognition_similarity'] = $similarity;
                } else {
                    // Enroll new face using Python service if available
                    $template = null;
                    if ($this->pythonVision->isAvailable() && isset($meta['python_face_detection']['detection_success']) && $meta['python_face_detection']['detection_success']) {
                        try {
                            $dbPath = storage_path('app/public/faces');
                            $this->pythonVision->createFaceDatabase($dbPath);
                            $enrollResult = $this->pythonVision->enrollFace($imageData, $driver->id, $dbPath);

                            if ($enrollResult['success']) {
                                $meta['python_face_enrollment'] = [
                                    'enrolled' => true,
                                    'filepath' => $enrollResult['filepath'],
                                    'faces_detected' => $enrollResult['faces_detected']
                                ];
                            }
                        } catch (\Exception $e) {
                            $meta['python_face_enrollment'] = ['error' => $e->getMessage()];
                        }
                    }

                    // Fallback to original enrollment
                    if ($template === null) {
                        $template = $this->faceService->enrollFaceForDriver($driver->id, $fullPath);
                    }

                    DriverFace::create([
                        'driver_id' => $driver->id,
                        'image_path' => $frontPath,
                        'face_template' => $template,
                        'created_by' => $user->id,
                    ]);
                    $meta['deepface_enrolled'] = true;
                    $meta['deepface_recognition_similarity'] = null;
                }

                $meta['deepface_recognition_configured'] = $this->faceService->deepfaceConfigured();
            }
        }

        if ($data['verification_method'] === 'id_only') {
            $proofMode = (string) ($data['proof_mode'] ?? 'selfie_with_id');
            $idFrontPath = null;
            $idBackPath = null;
            $selfieWithIdPath = null;

            if ($proofMode === 'upload_file') {
                if ($request->hasFile('id_front_file')) {
                    $idFrontPath = $request->file('id_front_file')->store('verification/id/'.$driver->id, 'public');
                }
                if ($request->hasFile('id_back_file')) {
                    $idBackPath = $request->file('id_back_file')->store('verification/id/'.$driver->id, 'public');
                }
            } else {
                $idFrontPath = ! empty($data['id_front_base64'])
                    ? $this->storeBase64Image($data['id_front_base64'], 'verification/id/'.$driver->id)
                    : null;
                $selfieWithIdPath = ! empty($data['face_selfie_base64'])
                    ? $this->storeBase64Image($data['face_selfie_base64'], 'verification/id/'.$driver->id)
                    : null;
                $idBackPath = ! empty($data['id_back_base64'])
                    ? $this->storeBase64Image($data['id_back_base64'], 'verification/id/'.$driver->id)
                    : null;
            }

            if (! $idFrontPath) {
                return back()->withErrors([
                    'id_front_file' => 'ID front image is required.',
                ]);
            }
            if ($proofMode === 'selfie_with_id' && ! $selfieWithIdPath) {
                return back()->withErrors([
                    'face_selfie_base64' => 'Selfie with ID is required for selfie mode.',
                ]);
            }

            $verification->id_image_path = $idFrontPath;
            $verification->selfie_with_id_path = $selfieWithIdPath;
            $verification->id_image_back_path = $idBackPath;

            $meta['proof_mode'] = $proofMode;
            $meta['id_type'] = $proofMode === 'selfie_with_id'
                ? 'ocr_auto_detect'
                : (string) ($data['id_type'] ?? 'other');
            // Enhanced OCR using Python Tesseract service
            $ocrResult = null;
            $idDetected = false;
            if ($this->pythonVision->isAvailable()) {
                try {
                    $fullIdPath = Storage::disk('public')->path($idFrontPath);
                    $imageData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($fullIdPath));
                    $ocrResult = $this->pythonVision->extractTextFromImage($imageData);

                    if ($ocrResult['success']) {
                        $meta['python_ocr'] = [
                            'text' => $ocrResult['text'],
                            'words' => $ocrResult['words'],
                            'avg_confidence' => $ocrResult['avg_confidence'],
                            'extraction_success' => true
                        ];

                        // Validate ID detection
                        $idDetected = $this->validateIdDetection($ocrResult['text'], $ocrResult['words']);
                        $meta['id_validation'] = $idDetected;
                    }
                } catch (\Exception $e) {
                    $meta['python_ocr'] = ['extraction_success' => false, 'error' => $e->getMessage()];
                }
            }

            // Fallback to original OCR service
            $fallbackOcr = $this->idOcrService->extractFromPublicPath($idFrontPath);
            $meta['ocr'] = $fallbackOcr;

            // Validate ID detection with fallback if Python service failed
            if (!$idDetected && $fallbackOcr) {
                $idDetected = $this->validateIdDetection($fallbackOcr['text'] ?? '', $fallbackOcr['words'] ?? []);
                $meta['id_validation'] = $idDetected;
            }

            // Return validation error if ID not detected properly
            if (!$idDetected['detected']) {
                return back()->withErrors([
                    'id_front_file' => $idDetected['error'] ?? 'Incorrect ID detected. Please select an option that corresponds/matches to your ID.',
                ]);
            }
        }

        if ($meta !== []) {
            $verification->manual_form_data = $meta;
        }

        $verification->save();

        if ($verification->status === 'approved') {
            return redirect()->intended('/dashboard')->with('status', 'Verification successful. Welcome!');
        }

        return redirect()->route('home')->with('status', 'Verification pending. Please stay on standby while admin review is in progress.');
    }

    private function storeBase64Image(?string $dataUrl, string $dir): ?string
    {
        if (! $dataUrl || ! str_starts_with($dataUrl, 'data:image')) {
            return null;
        }

        [$meta, $content] = explode(',', $dataUrl, 2) + [null, null];
        if (! $content) {
            return null;
        }

        $ext = 'jpg';
        if ($meta && str_contains($meta, 'png')) {
            $ext = 'png';
        } elseif ($meta && (str_contains($meta, 'jpeg') || str_contains($meta, 'jpg'))) {
            $ext = 'jpg';
        } elseif ($meta && str_contains($meta, 'webp')) {
            $ext = 'webp';
        }

        $path = trim($dir, '/').'/'.Str::uuid().'.'.$ext;
        Storage::disk('public')->put($path, base64_decode($content));

        return $path;
    }

    /**
     * Validate ID detection from OCR text and words
     */
    private function validateIdDetection(string $text, array $words): array
    {
        $detected = false;
        $error = null;

        // Common ID indicators in OCR text
        $idIndicators = [
            'license', 'licence', 'identification', 'passport', 'national id',
            'driver license', 'driving license', 'state id', 'government',
            'official', 'document', 'card', 'permit'
        ];

        $lowerText = strtolower($text);
        $lowerWords = array_map('strtolower', $words);

        // Check for ID indicators in text
        foreach ($idIndicators as $indicator) {
            if (str_contains($lowerText, $indicator)) {
                $detected = true;
                break;
            }
        }

        // Check for common ID patterns
        if (!$detected) {
            // Look for patterns like "DL-", "ID#", etc.
            if (preg_match('/(dl[-\s]*|id[-\s]*|passport[-\s]*|license[-\s]*)/i', $text)) {
                $detected = true;
            }
        }

        // Check for name and date patterns (common in IDs)
        if (!$detected) {
            $hasName = false;
            $hasDate = false;

            foreach ($lowerWords as $word) {
                if (preg_match('/^[a-z\s]+$/i', $word) && strlen($word) > 2) {
                    $hasName = true;
                }
                if (preg_match('/\d{4}|\d{2}\/\d{2}|\d{2}-\d{2}-\d{2}/', $word)) {
                    $hasDate = true;
                }
            }

            if ($hasName && $hasDate) {
                $detected = true;
            }
        }

        if (!$detected) {
            $error = 'No valid ID document detected. Please upload a clear photo of your government-issued ID.';
        }

        return [
            'detected' => $detected,
            'error' => $error,
            'text_length' => strlen($text),
            'word_count' => count($words),
            'indicators_found' => array_intersect($idIndicators, $lowerWords)
        ];
    }
}
