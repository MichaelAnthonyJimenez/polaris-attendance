<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LocationSafetyAlertNotification;
use App\Services\Email\TransactionalEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class LocationController extends Controller
{
    private const LIVE_LOCATION_CACHE_KEY_PREFIX = 'driver_live_location:';
    private const LOCATION_ALERT_THROTTLE_SECONDS = 600;

    public function index(Request $request): View
    {
        $selectedDriverId = (int) $request->integer('driver_id', 0);

        $drivers = User::select('id', 'name', 'badge_number', 'active', 'location_sharing_enabled', 'latitude', 'longitude', 'location_updated_at')
            ->where('role', 'driver')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $validDriverIds = $drivers->pluck('id')->all();
        if ($selectedDriverId > 0 && ! in_array($selectedDriverId, $validDriverIds, true)) {
            $selectedDriverId = 0;
        }

        $locationLogs = $this->buildLocationLogs($selectedDriverId);
        $liveFeed = $this->buildLiveFeed($selectedDriverId);

        return view('locations.index', [
            'drivers' => $drivers,
            'selectedDriverId' => $selectedDriverId,
            'locationLogs' => $locationLogs,
            'liveFeed' => $liveFeed,
        ]);
    }

    public function liveFeed(Request $request): JsonResponse
    {
        $selectedDriverId = (int) $request->integer('driver_id', 0);

        return response()->json([
            'liveFeed' => $this->buildLiveFeed($selectedDriverId),
        ]);
    }

    public function liveUpdate(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'driver') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $user->location_sharing_enabled) {
            return response()->json([
                'message' => 'Location sharing is disabled for this driver.',
                'location_sharing_enabled' => false,
            ], 409);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geo_accuracy' => ['nullable', 'numeric', 'min:0'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
        ]);

        $user->latitude = (float) $validated['latitude'];
        $user->longitude = (float) $validated['longitude'];
        $user->location_updated_at = now();
        $user->save();

        $cacheKey = self::LIVE_LOCATION_CACHE_KEY_PREFIX . $user->id;
        $existing = Cache::get($cacheKey, []);
        $points = is_array($existing['points'] ?? null) ? $existing['points'] : [];

        $points[] = [
            'lat' => (float) $validated['latitude'],
            'lng' => (float) $validated['longitude'],
            'geo_accuracy' => isset($validated['geo_accuracy']) ? (float) $validated['geo_accuracy'] : null,
            'speed' => isset($validated['speed']) ? (float) $validated['speed'] : null,
            'heading' => isset($validated['heading']) ? (float) $validated['heading'] : null,
            'captured_at' => now()->toIso8601String(),
        ];

        $points = array_slice($points, -50);
        Cache::put($cacheKey, ['points' => $points], now()->addHours(6));

        $risk = $this->assessLocationRisk($validated);
        if ($risk !== null) {
            $this->notifyAdminsForLocationRisk($user, $validated, $risk);
        }

        return response()->json([
            'ok' => true,
            'location_sharing_enabled' => true,
            'location_risk' => $risk,
        ]);
    }

    public function enableSharing(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'driver') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $user->location_sharing_enabled) {
            $user->location_sharing_enabled = true;
            $user->save();
        }

        return response()->json([
            'ok' => true,
            'location_sharing_enabled' => true,
            'message' => 'Location sharing enabled.',
        ]);
    }

    private function buildLocationLogs(int $selectedDriverId)
    {
        return Attendance::query()
            ->with(['driver:id,name,badge_number,active'])
            ->whereIn('type', ['check_in', 'check_out'])
            ->whereNotNull('meta->latitude')
            ->whereNotNull('meta->longitude')
            ->whereHas('driver', fn ($query) => $query->where('role', 'driver'))
            ->when($selectedDriverId > 0, fn ($query) => $query->where('driver_id', $selectedDriverId))
            ->latest('captured_at')
            ->limit(500)
            ->get()
            ->map(function (Attendance $attendance) {
                $meta = is_array($attendance->meta) ? $attendance->meta : [];
                $latitude = isset($meta['latitude']) ? (float) $meta['latitude'] : null;
                $longitude = isset($meta['longitude']) ? (float) $meta['longitude'] : null;
                $accuracy = isset($meta['geo_accuracy']) ? (float) $meta['geo_accuracy'] : null;

                if ($latitude === null || $longitude === null) {
                    return null;
                }

                return [
                    'attendance_id' => $attendance->id,
                    'driver_id' => $attendance->driver_id,
                    'driver_name' => $attendance->driver?->name ?? 'Unknown Driver',
                    'driver_badge' => $attendance->driver?->badge_number ?? 'N/A',
                    'driver_active' => (bool) ($attendance->driver?->active ?? false),
                    'type' => $attendance->type,
                    'type_label' => $attendance->type === 'check_in' ? 'Check In' : 'Check Out',
                    'captured_at' => $attendance->captured_at?->toIso8601String(),
                    'captured_label' => $attendance->captured_at?->format('M d, Y h:i A') ?? 'N/A',
                    'lat' => $latitude,
                    'lng' => $longitude,
                    'geo_accuracy' => $accuracy,
                ];
            })
            ->filter()
            ->values();
    }

    private function buildLiveFeed(int $selectedDriverId): array
    {
        $drivers = User::query()
            ->select('id', 'name', 'badge_number', 'location_sharing_enabled', 'latitude', 'longitude', 'location_updated_at')
            ->where('role', 'driver')
            ->where('active', true)
            ->when($selectedDriverId > 0, fn ($q) => $q->where('id', $selectedDriverId))
            ->orderBy('name')
            ->get();

        return $drivers->map(function (User $driver) {
            $cacheKey = self::LIVE_LOCATION_CACHE_KEY_PREFIX . $driver->id;
            $existing = Cache::get($cacheKey, []);
            $routePoints = array_values(is_array($existing['points'] ?? null) ? $existing['points'] : []);

            $latestPoint = ! empty($routePoints) ? end($routePoints) : null;
            $hasLiveCoordinates = $driver->latitude !== null && $driver->longitude !== null;
            $isSharing = (bool) $driver->location_sharing_enabled;

            return [
                'driver_id' => $driver->id,
                'driver_name' => $driver->name,
                'driver_badge' => $driver->badge_number ?: 'N/A',
                'sharing_enabled' => $isSharing,
                'status' => $isSharing ? ($hasLiveCoordinates ? 'Live' : 'Waiting for GPS') : 'Location Off',
                'lat' => $hasLiveCoordinates ? (float) $driver->latitude : null,
                'lng' => $hasLiveCoordinates ? (float) $driver->longitude : null,
                'location_updated_at' => $driver->location_updated_at?->toIso8601String(),
                'location_updated_label' => $driver->location_updated_at?->format('M d, Y h:i A') ?? 'No live update yet',
                'route_points' => $routePoints,
                'route_label' => count($routePoints) >= 2
                    ? 'Route in progress (' . count($routePoints) . ' points)'
                    : ($latestPoint ? 'Current position acquired' : 'No route data yet'),
                'latest_speed' => isset($latestPoint['speed']) ? (float) $latestPoint['speed'] : null,
                'latest_heading' => isset($latestPoint['heading']) ? (float) $latestPoint['heading'] : null,
            ];
        })->values()->all();
    }

    /**
     * @param array{latitude:float|int|string,longitude:float|int|string,geo_accuracy?:float|int|string|null,speed?:float|int|string|null,heading?:float|int|string|null} $validated
     * @return array{code:string,title:string,message:string,severity:string}|null
     */
    private function assessLocationRisk(array $validated): ?array
    {
        $accuracy = isset($validated['geo_accuracy']) ? (float) $validated['geo_accuracy'] : null;
        $speedMps = isset($validated['speed']) ? (float) $validated['speed'] : null;
        $speedKph = $speedMps !== null ? ($speedMps * 3.6) : null;

        if ($speedKph !== null && $speedKph >= 50) {
            return [
                'code' => 'dangerous_area',
                'title' => 'Dangerous area movement',
                'severity' => 'high',
                'message' => 'Driver appears to be moving fast on a road/highway segment. Remind the driver to stop in a safe area before check in/check out.',
            ];
        }

        if ($accuracy !== null && $accuracy >= 800) {
            return [
                'code' => 'unoperational_area',
                'title' => 'Unoperational area signal',
                'severity' => 'high',
                'message' => 'GPS accuracy is very poor and may indicate an unoperational area. Attendance location reliability is low.',
            ];
        }

        if ($accuracy !== null && $accuracy >= 500) {
            return [
                'code' => 'unreachable_area',
                'title' => 'Unreachable area signal',
                'severity' => 'medium',
                'message' => 'GPS accuracy is poor and may indicate an unreachable/weak-coverage area.',
            ];
        }

        if ($accuracy !== null && $accuracy >= 150) {
            return [
                'code' => 'limited_data_area',
                'title' => 'Limited data area',
                'severity' => 'medium',
                'message' => 'GPS accuracy is degraded, likely due to limited data/signal conditions.',
            ];
        }

        return null;
    }

    /**
     * @param array{latitude:float|int|string,longitude:float|int|string,geo_accuracy?:float|int|string|null,speed?:float|int|string|null,heading?:float|int|string|null} $validated
     * @param array{code:string,title:string,message:string,severity:string} $risk
     */
    private function notifyAdminsForLocationRisk(User $driver, array $validated, array $risk): void
    {
        $throttleKey = 'location_risk_alert:' . $driver->id . ':' . $risk['code'];
        if (Cache::has($throttleKey)) {
            return;
        }
        Cache::put($throttleKey, true, now()->addSeconds(self::LOCATION_ALERT_THROTTLE_SECONDS));

        $channel = (string) Setting::get('attendance_notification_channel', 'both');
        $sendApp = in_array($channel, ['app', 'both'], true);
        $sendEmail = in_array($channel, ['email', 'both'], true);

        $admins = User::query()
            ->where('role', 'admin')
            ->where('active', true)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $accuracy = isset($validated['geo_accuracy']) ? (float) $validated['geo_accuracy'] : null;
        $speedMps = isset($validated['speed']) ? (float) $validated['speed'] : null;
        $speedKph = $speedMps !== null ? round($speedMps * 3.6, 1) : null;

        if ($sendApp) {
            Notification::send($admins, new LocationSafetyAlertNotification(
                $driver,
                $risk,
                $latitude,
                $longitude,
                $accuracy,
                $speedKph
            ));
        }

        if ($sendEmail) {
            $emails = $admins
                ->filter(fn (User $admin) => is_string($admin->email) && trim($admin->email) !== '')
                ->map(fn (User $admin) => ['email' => $admin->email, 'name' => $admin->name])
                ->values()
                ->all();
            if ($emails !== []) {
                $subject = 'Driver location risk alert: ' . ($driver->name ?? 'Driver');
                $html = '<h2>Driver Location Risk Alert</h2>'
                    . '<p><strong>Driver:</strong> ' . e((string) ($driver->name ?? 'Unknown')) . '</p>'
                    . '<p><strong>Alert:</strong> ' . e($risk['title']) . '</p>'
                    . '<p>' . e($risk['message']) . '</p>'
                    . '<p><strong>Coordinates:</strong> ' . e((string) $latitude) . ', ' . e((string) $longitude) . '</p>'
                    . '<p><strong>GPS accuracy:</strong> ' . e($accuracy !== null ? (string) round($accuracy, 1) . ' m' : 'N/A') . '</p>'
                    . '<p><strong>Speed:</strong> ' . e($speedKph !== null ? (string) $speedKph . ' km/h' : 'N/A') . '</p>'
                    . '<p><strong>Time:</strong> ' . e(now()->format('Y-m-d H:i:s')) . '</p>';

                try {
                    app(TransactionalEmailService::class)->send($emails, $subject, $html);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }
}

