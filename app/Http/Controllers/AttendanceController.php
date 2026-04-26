<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Attendance;
use App\Models\User;
use App\Notifications\AttendanceNotification;
use App\Services\FaceRecognitionService;
use App\Services\Location\RouteComplianceService;
use App\Services\LivenessService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Mail\DriverAttendanceMail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private FaceRecognitionService $faceService,
        private LivenessService $livenessService,
        private RouteComplianceService $routeComplianceService,
    ) {
    }

    public function index(Request $request): View
    {
        $driverSelfId = null;
        $driverCreatedAtStart = null;
        $adminChartData = null;
        $driverPerformance = null;
        $driverHistoryData = null;
        $search = trim((string) $request->get('search', ''));
        $role = is_string(Auth::user()?->role) ? mb_strtolower(trim((string) Auth::user()?->role)) : '';

        if ($role === 'driver') {
            $driver = User::query()->where('id', Auth::id())->where('role', 'driver')->first();
            $driverSelfId = $driver->id;
            $driverCreatedAtStart = $driver?->created_at?->copy()?->startOfDay();
        }

        if ($role === 'admin') {
            $today = Carbon::today();
            $thisWeekStart = Carbon::today()->startOfWeek();
            $thisWeekEnd = Carbon::today()->endOfWeek();
            $monthStart = Carbon::today()->startOfMonth();
            $monthEnd = Carbon::today()->endOfMonth();

            $presentToday = Attendance::whereDate('captured_at', $today)
                ->where('type', 'check_in')
                ->get()
                ->filter(fn ($a) => ($a->status ?? null) === 'Present')
                ->count();
            $lateToday = Attendance::whereDate('captured_at', $today)
                ->where('type', 'check_in')
                ->get()
                ->filter(fn ($a) => ($a->status ?? null) === 'Late')
                ->count();
            $absentToday = User::where('role', 'driver')->where('active', true)
                ->where('created_at', '<', $today->copy()->startOfDay())
                ->whereDoesntHave('attendances', fn ($q) => $q->whereDate('captured_at', $today)->where('type', 'check_in'))
                ->count();

            $presentWeek = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])
                ->where('type', 'check_in')
                ->get()
                ->filter(fn ($a) => ($a->status ?? null) === 'Present')
                ->count();
            $lateWeek = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])
                ->where('type', 'check_in')
                ->get()
                ->filter(fn ($a) => ($a->status ?? null) === 'Late')
                ->count();
            $absentWeek = User::where('role', 'driver')->where('active', true)
                ->where('created_at', '<', $thisWeekStart)
                ->whereDoesntHave('attendances', fn ($q) => $q->whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])->where('type', 'check_in'))
                ->count();

            $presentMonth = Attendance::whereBetween('captured_at', [$monthStart, $monthEnd])
                ->where('type', 'check_in')
                ->get()
                ->filter(fn ($a) => ($a->status ?? null) === 'Present')
                ->count();
            $lateMonth = Attendance::whereBetween('captured_at', [$monthStart, $monthEnd])
                ->where('type', 'check_in')
                ->get()
                ->filter(fn ($a) => ($a->status ?? null) === 'Late')
                ->count();
            $absentMonth = User::where('role', 'driver')->where('active', true)
                ->where('created_at', '<', $monthStart)
                ->whereDoesntHave('attendances', fn ($q) => $q->whereBetween('captured_at', [$monthStart, $monthEnd])->where('type', 'check_in'))
                ->count();

            $adminChartData = [
                'today' => ['present' => $presentToday, 'late' => $lateToday, 'absent' => $absentToday],
                'week' => ['present' => $presentWeek, 'late' => $lateWeek, 'absent' => $absentWeek],
                'month' => ['present' => $presentMonth, 'late' => $lateMonth, 'absent' => $absentMonth],
            ];
        }

        $showAttendanceHistory = (bool) Setting::get('show_attendance_history', true);

        if ($driverSelfId && $showAttendanceHistory) {
            $historyRows = Attendance::where('driver_id', $driverSelfId)
                ->latest('captured_at')
                ->limit(500)
                ->get();

            $todayStart = Carbon::today()->startOfDay();
            $calendarAnchor = Carbon::today()->startOfMonth();
            $historyMonthParam = $request->query('history_month');
            if (is_string($historyMonthParam) && preg_match('/^\d{4}-\d{2}$/', $historyMonthParam)) {
                try {
                    $parsed = Carbon::createFromFormat('Y-m', $historyMonthParam)->startOfMonth();
                    if ($parsed->lte($todayStart->copy()->startOfMonth())) {
                        $calendarAnchor = $parsed;
                    }
                } catch (\Throwable) {
                    // keep current month
                }
            }

            $monthStart = $calendarAnchor->copy()->startOfMonth();
            $monthEnd = $calendarAnchor->copy()->endOfMonth();
            $monthRows = Attendance::where('driver_id', $driverSelfId)
                ->whereBetween('captured_at', [$monthStart, $monthEnd])
                ->get();

            $hasAttendanceStatus = Schema::hasColumn('attendances', 'status');

            $calendarRows = $monthRows->groupBy(fn ($row) => $row->captured_at->format('Y-m-d'));
            $calendarDays = [];
            $daysInMonth = $monthStart->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayCarbon = $monthStart->copy()->day($d);
                $dateKey = $dayCarbon->format('Y-m-d');
                $rows = collect($calendarRows->get($dateKey, []));
                $checkIn = $rows->first(fn ($r) => $r->type === 'check_in');
                if ($checkIn) {
                    if (! $hasAttendanceStatus) {
                        $calendarDays[$dateKey] = 'present';
                        continue;
                    }
                    $raw = $checkIn->status;
                    $st = is_string($raw) ? trim($raw) : null;
                    $lower = ($st !== null && $st !== '') ? mb_strtolower($st) : '';
                    if ($lower === 'late') {
                        $calendarDays[$dateKey] = 'late';
                    } elseif ($lower === 'present') {
                        $calendarDays[$dateKey] = 'present';
                    } elseif ($lower === 'absent') {
                        $calendarDays[$dateKey] = 'absent';
                    } else {
                        // Pending, empty, unknown → no calendar color
                        $calendarDays[$dateKey] = null;
                    }
                    continue;
                }
                if ($driverCreatedAtStart && $dayCarbon->startOfDay()->lt($driverCreatedAtStart)) {
                    $calendarDays[$dateKey] = null;
                } elseif ($dayCarbon->startOfDay()->lt($todayStart)) {
                    $calendarDays[$dateKey] = 'absent';
                } else {
                    $calendarDays[$dateKey] = null;
                }
            }

            $prevMonthYm = $calendarAnchor->copy()->subMonth()->format('Y-m');
            $nextMonthCandidate = $calendarAnchor->copy()->addMonth();
            $canGoNext = $nextMonthCandidate->lte($todayStart->copy()->startOfMonth());

            $driverHistoryData = [
                'history' => $historyRows->map(function ($row) use ($hasAttendanceStatus) {
                    return [
                        'type' => $row->type,
                        'captured_at' => $row->captured_at?->toIso8601String(),
                        'captured_label' => $row->captured_at?->format('M d, Y h:i A'),
                        'status' => $hasAttendanceStatus ? ($row->status ?? null) : null,
                        'face_confidence' => $row->face_confidence,
                        'liveness_score' => $row->liveness_score,
                        'device_id' => $row->device_id,
                        'total_hours' => $row->total_hours !== null ? (float) $row->total_hours : null,
                    ];
                })->values()->all(),
                'calendar' => [
                    'year' => (int) $monthStart->format('Y'),
                    'month' => (int) $monthStart->format('n'),
                    'yearMonth' => $monthStart->format('Y-m'),
                    'monthName' => $monthStart->format('F Y'),
                    'daysInMonth' => $daysInMonth,
                    'firstDayOfWeek' => $monthStart->copy()->startOfMonth()->dayOfWeek,
                    'days' => $calendarDays,
                    'prevMonth' => $prevMonthYm,
                    'nextMonth' => $canGoNext ? $nextMonthCandidate->format('Y-m') : null,
                    'canGoNext' => $canGoNext,
                ],
            ];
        }

        $attendancesQuery = Attendance::with('driver')
            ->when($driverSelfId, fn ($q) => $q->where('driver_id', $driverSelfId))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('type', 'like', $like)
                        ->orWhere('device_id', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhereHas('driver', fn ($dq) => $dq->where('name', 'like', $like));
                });
            })
            ->latest('captured_at');

        return view('attendance.index', [
            'attendances' => $attendancesQuery->paginate(20)->withQueryString(),
            'drivers' => User::where('role', 'driver')->where('active', true)->orderBy('name')->get(),
            'driverSelfId' => $driverSelfId,
            'adminChartData' => $adminChartData,
            'driverPerformance' => $driverPerformance,
            'driverHistoryData' => $showAttendanceHistory ? $driverHistoryData : null,
            'search' => $search,
            'showAttendanceHistory' => $showAttendanceHistory,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = is_string(Auth::user()?->role) ? mb_strtolower(trim((string) Auth::user()?->role)) : '';
        if ($role === 'driver') {
            $driver = User::query()->where('id', Auth::id())->where('role', 'driver')->first();
            $request->merge(['driver_id' => $driver->id]);
        }

        $requirePhotoAttendance = (bool) Setting::get('require_photo_attendance', false);

        $data = $request->validate([
            'driver_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:check_in,check_out'],
            'captured_at' => ['nullable', 'date'],
            'captured_timezone' => ['nullable', 'string', 'max:64'],
            'captured_tz_offset' => ['nullable', 'integer'],
            'face_image' => [$requirePhotoAttendance ? 'required_without:face_image_data' : 'nullable', 'image', 'max:5120'],
            'face_image_data' => [$requirePhotoAttendance ? 'required_without:face_image' : 'nullable', 'string'], // base64 from camera
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geo_accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $imagePath = null;
        $confidence = null;
        $rawConfidence = null;
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

        $faceRecognitionEnabled = (bool) Setting::get('face_recognition_enabled', true);
        $livenessDetectionEnabled = (bool) Setting::get('liveness_detection_enabled', true);
        $minFaceConfidence = (float) Setting::get('min_face_confidence', 70);
        $minLivenessScore = (float) Setting::get('min_liveness_score', 0.5);
        $effectiveMinFaceConfidence = $this->adaptiveFaceConfidenceThreshold($minFaceConfidence, $fullPath);

        if ($fullPath && $faceRecognitionEnabled) {
            $confidence = $this->faceService->matchLatestForDriver($data['driver_id'], $fullPath);
            $rawConfidence = $confidence;
            // DeepFace-style similarity may come as 0..1; normalize to 0..100 for configured thresholds/UI.
            if ($confidence !== null && $confidence <= 1) {
                $confidence = round($confidence * 100, 2);
            }
        }

        if ($fullPath && $livenessDetectionEnabled) {
            $liveness = $this->livenessService->score($fullPath);
        }

        $strictFaceRejectThreshold = max(25.0, $effectiveMinFaceConfidence - 20.0);
        $isFaceScoreLow = $faceRecognitionEnabled
            && $confidence !== null
            && $confidence < $strictFaceRejectThreshold;

        if ($livenessDetectionEnabled && $liveness !== null && $liveness < $minLivenessScore) {
            return back()->withErrors([
                'face_image' => sprintf('Liveness score is too low (%.2f). Minimum required is %.2f.', $liveness, $minLivenessScore),
            ])->withInput();
        }

        if ($isFaceScoreLow) {
            return back()->withErrors([
                'face_image' => 'Unknown user, unable to ' . str_replace('_', ' ', (string) $data['type']) . '.',
            ])->withInput();
        }

        // Automatically detect device identifier
        $deviceId = $this->detectDevice($request);

        $isDriverSharingEnabled = true;
        if ($role === 'driver') {
            $isDriverSharingEnabled = (bool) (Auth::user()?->location_sharing_enabled ?? false);
        }

        $meta = array_filter([
            'captured_by' => $request->user()?->id,
            'captured_timezone' => isset($data['captured_timezone']) && is_string($data['captured_timezone']) ? trim($data['captured_timezone']) : null,
            'captured_tz_offset' => isset($data['captured_tz_offset']) ? (int) $data['captured_tz_offset'] : null,
            'latitude' => $isDriverSharingEnabled && isset($data['latitude']) ? (float) $data['latitude'] : null,
            'longitude' => $isDriverSharingEnabled && isset($data['longitude']) ? (float) $data['longitude'] : null,
            'geo_accuracy' => $isDriverSharingEnabled && isset($data['geo_accuracy']) ? (float) $data['geo_accuracy'] : null,
            'face_score_low' => $isFaceScoreLow ? true : null,
            'face_score_required' => $isFaceScoreLow ? $strictFaceRejectThreshold : null,
            'face_score_base_required' => $isFaceScoreLow ? $minFaceConfidence : null,
        ], static fn ($v) => $v !== null && $v !== '');

        $requireLocationCheck = (bool) Setting::get('require_location_checkin', false);
        $routeCompliance = $this->routeComplianceService->evaluate(
            (int) $data['driver_id'],
            $meta['latitude'] ?? null,
            $meta['longitude'] ?? null,
            $meta['geo_accuracy'] ?? null
        );
        $meta['route_compliance'] = $routeCompliance;

        if ($requireLocationCheck && ($routeCompliance['status'] ?? null) === 'outside_buffer') {
            return back()->withErrors([
                'latitude' => 'Your current location is outside your operational route.',
            ])->withInput();
        }

        $capturedAt = now();
        if (! empty($data['captured_at'])) {
            try {
                // Parse the captured_at time and convert to Asia/Manila timezone
                $capturedAt = Carbon::parse((string) $data['captured_at']);

                // If timezone offset is provided, adjust the time accordingly
                if (isset($data['captured_tz_offset']) && is_numeric($data['captured_tz_offset'])) {
                    $capturedAt->addMinutes(-$data['captured_tz_offset']);
                }

                // Convert to Asia/Manila timezone
                $capturedAt->setTimezone('Asia/Manila');
            } catch (\Throwable) {
                $capturedAt = now()->setTimezone('Asia/Manila');
            }
        } else {
            // Ensure we always use Asia/Manila timezone
            $capturedAt = now()->setTimezone('Asia/Manila');
        }

        $totalHours = null;
        if ($data['type'] === 'check_out') {
            $pairedCheckIn = Attendance::findPairedCheckInForCheckout((int) $data['driver_id'], $capturedAt);
            if ($pairedCheckIn) {
                $totalHours = Attendance::hoursBetween($pairedCheckIn->captured_at, $capturedAt);
            }
        }

        $attendance = Attendance::create([
            'driver_id' => $data['driver_id'],
            'type' => $data['type'],
            'captured_at' => $capturedAt,
            'face_confidence' => $confidence,
            'liveness_score' => $liveness,
            'image_path' => $imagePath,
            'device_id' => $deviceId,
            'synced' => true,
            'meta' => $meta,
            'total_hours' => $totalHours,
        ]);

        $driver = User::find($data['driver_id']);
        $auditNew = ['type' => $data['type'], 'driver' => $driver->name];
        if ($totalHours !== null) {
            $auditNew['total_hours'] = $totalHours;
        }
        AuditLogger::log('created', 'Attendance', $attendance->id, null, $auditNew, "Attendance {$data['type']} recorded for {$driver->name}");

        $attendanceNotificationChannel = (string) Setting::get('attendance_notification_channel', 'both');
        $wantEmail = in_array($attendanceNotificationChannel, ['both', 'email'], true);
        $wantApp = in_array($attendanceNotificationChannel, ['both', 'app'], true);

        if ($role === 'driver') {
            $user = Auth::user();
            if ($wantEmail && $user && Setting::get('driver_email_notifications') && $user->email) {
                $want = $data['type'] === 'check_in'
                    ? Setting::get('driver_email_on_checkin', true)
                    : Setting::get('driver_email_on_checkout', true);
                if ($want) {
                    try {
                        Mail::to($user->email)->send(new DriverAttendanceMail($user, $attendance));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }

            if ($wantApp && $user instanceof User) {
                $user->notify(new AttendanceNotification(
                    $attendance,
                    'Attendance ' . str_replace('_', ' ', $data['type']) . ' recorded successfully.'
                ));
            }

            $adminRecipients = User::query()
                ->where('role', 'admin')
                ->where('active', true)
                ->get();
            if ($wantApp && $adminRecipients->isNotEmpty()) {
                Notification::send($adminRecipients, new AttendanceNotification(
                    $attendance,
                    ($driver?->name ?? 'Driver') . ' recorded ' . str_replace('_', ' ', $data['type']) . '.'
                ));
            }

            return redirect()->route('camera.index')
                ->with('status', 'Attendance saved')
                ->with('attendance_recorded', $data['type']);
        }

        $authUser = Auth::user();
        if ($wantApp && $authUser instanceof User) {
            $authUser->notify(new AttendanceNotification(
                $attendance,
                'Attendance ' . str_replace('_', ' ', $data['type']) . ' recorded.'
            ));
        }

        return redirect()->route('attendance.index')->with('status', 'Attendance saved');
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load('driver');

        return view('attendance.show', [
            'attendance' => $attendance,
        ]);
    }

    public function history(Request $request): View
    {
        $user = Auth::user();
        if (! $user || mb_strtolower((string) ($user->role ?? '')) !== 'driver') {
            abort(403);
        }

        $period = (string) $request->query('period', 'daily');
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'daily';
        }

        $rows = Attendance::query()
            ->where('driver_id', $user->id)
            ->orderByDesc('captured_at')
            ->limit(500)
            ->get();

        $grouped = match ($period) {
            'weekly' => $rows->groupBy(fn (Attendance $row) => $row->captured_at?->copy()->startOfWeek()->format('Y-m-d') ?? 'Unknown'),
            'monthly' => $rows->groupBy(fn (Attendance $row) => $row->captured_at?->format('Y-m') ?? 'Unknown'),
            default => $rows->groupBy(fn (Attendance $row) => $row->captured_at?->format('Y-m-d') ?? 'Unknown'),
        };

        return view('attendance.history', [
            'period' => $period,
            'groupedHistory' => $grouped,
        ]);
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

    /**
     * Lower required face confidence for lower-resolution captures.
     * Helps support low-end cameras while preserving a sensible minimum.
     */
    private function adaptiveFaceConfidenceThreshold(float $baseThreshold, ?string $absoluteImagePath): float
    {
        if (! $absoluteImagePath || ! is_file($absoluteImagePath)) {
            return $baseThreshold;
        }

        $imageSize = @getimagesize($absoluteImagePath);
        if (! is_array($imageSize) || ! isset($imageSize[0], $imageSize[1])) {
            return $baseThreshold;
        }

        $width = (int) $imageSize[0];
        $height = (int) $imageSize[1];
        $shortSide = min($width, $height);

        // Start with configured threshold, then relax for lower-quality captures.
        $effective = $baseThreshold;
        if ($shortSide <= 240) {
            $effective -= 25; // very low-end camera or compressed image
        } elseif ($shortSide <= 360) {
            $effective -= 18; // low-end front camera
        } elseif ($shortSide <= 480) {
            $effective -= 12; // standard low-mid resolution
        }

        // Keep hard lower bound to avoid accepting very weak matches.
        return max(35.0, round($effective, 2));
    }
}

