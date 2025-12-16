<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Attendance;
use App\Models\Driver;
use App\Services\FaceRecognitionService;
use App\Services\LivenessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private FaceRecognitionService $faceService,
        private LivenessService $livenessService
    ) {
    }

    public function index(): View
    {
        $driverSelfId = null;

        if (Auth::user()?->role === 'driver') {
            // Ensure a driver record exists for this user
            $driver = Driver::firstOrCreate(
                ['badge_number' => 'user-'.Auth::id()],
                ['name' => Auth::user()->name ?? 'Driver '.Auth::id(), 'active' => true]
            );
            $driverSelfId = $driver->id;
        }

        return view('attendance.index', [
            'attendances' => Attendance::with('driver')
                ->latest('captured_at')
                ->paginate(20),
            'drivers' => Driver::where('active', true)->orderBy('name')->get(),
            'driverSelfId' => $driverSelfId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()?->role === 'driver') {
            // Auto-assign driver_id for drivers
            $driver = Driver::firstOrCreate(
                ['badge_number' => 'user-'.Auth::id()],
                ['name' => Auth::user()->name ?? 'Driver '.Auth::id(), 'active' => true]
            );
            $request->merge(['driver_id' => $driver->id]);
        }

        $data = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'type' => ['required', 'in:check_in,check_out'],
            'face_image' => ['nullable', 'image', 'max:5120'],
            'face_image_data' => ['nullable', 'string'], // base64 from camera
        ]);

        $imagePath = null;
        $confidence = null;
        $liveness = null;

        $fullPath = null;

        if ($request->hasFile('face_image')) {
            $imagePath = $request->file('face_image')->store('attendance', 'public');
            $fullPath = Storage::disk('public')->path($imagePath);
        } elseif (! empty($data['face_image_data']) && str_starts_with($data['face_image_data'], 'data:image')) {
            // Save base64 camera capture to storage
            [$meta, $content] = explode(',', $data['face_image_data'], 2);
            $extension = str_contains($meta, 'jpeg') ? 'jpg' : (str_contains($meta, 'png') ? 'png' : 'png');
            $filename = 'attendance/'.Str::uuid().'.'.$extension;
            Storage::disk('public')->put($filename, base64_decode($content));
            $imagePath = $filename;
            $fullPath = Storage::disk('public')->path($imagePath);
        }

        if ($fullPath) {
            $confidence = $this->faceService->matchLatestForDriver($data['driver_id'], $fullPath);
            $liveness = $this->livenessService->score($fullPath);
        }

        // Automatically detect device identifier
        $deviceId = $this->detectDevice($request);

        $attendance = Attendance::create([
            'driver_id' => $data['driver_id'],
            'type' => $data['type'],
            'captured_at' => now(),
            'face_confidence' => $confidence,
            'liveness_score' => $liveness,
            'image_path' => $imagePath,
            'device_id' => $deviceId,
            'synced' => true,
            'meta' => [
                'captured_by' => $request->user()?->id,
            ],
        ]);

        $driver = Driver::find($data['driver_id']);
        AuditLogger::log('created', 'Attendance', $attendance->id, null, ['type' => $data['type'], 'driver' => $driver->name], "Attendance {$data['type']} recorded for {$driver->name}");

        return redirect()->route('attendance.index')->with('status', 'Attendance saved');
    }

    /**
     * Automatically detect device identifier based on browser and IP
     */
    private function detectDevice(Request $request): string
    {
        $userAgent = $request->userAgent() ?? 'unknown';
        $ip = $request->ip() ?? '0.0.0.0';
        
        // Create a device fingerprint from user agent and IP
        // This creates a consistent identifier for the same device/browser
        $fingerprint = hash('sha256', $userAgent . '|' . $ip);
        
        // Return a shortened, readable identifier (first 12 chars)
        return 'device-' . substr($fingerprint, 0, 12);
    }
}

