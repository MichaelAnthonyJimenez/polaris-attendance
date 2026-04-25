<?php

namespace App\Http\Controllers;

use App\Models\DriverFace;
use App\Models\DriverVerification;
use App\Models\User;
use App\Services\FaceRecognitionService;
use App\Services\LivenessService;
use App\Services\Ocr\IdOcrService;
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

                $hasExistingFace = DriverFace::where('driver_id', $driver->id)->exists();

                if ($hasExistingFace) {
                    $similarity = $this->faceService->matchLatestForDriver($driver->id, $fullPath);
                    $meta['deepface_recognition_similarity'] = $similarity;
                } else {
                    $template = $this->faceService->enrollFaceForDriver($driver->id, $fullPath);
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

            // Handle OCR confirmation data if present
            if ($request->has('idv_confirmed_text')) {
                $meta['ocr'] = [
                    'text' => $request->input('idv_confirmed_text'),
                    'confirmed' => true,
                    'method' => 'optiic'
                ];
            } elseif ($request->has('idv_confirmed_name')) {
                $meta['ocr'] = [
                    'name' => $request->input('idv_confirmed_name'),
                    'id_number' => $request->input('idv_confirmed_id_number'),
                    'address' => $request->input('idv_confirmed_address'),
                    'birth_date' => $request->input('idv_confirmed_birth_date'),
                    'confirmed' => true
                ];
            } else {
                $meta['ocr'] = $this->idOcrService->extractFromPublicPath($idFrontPath);
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
}
