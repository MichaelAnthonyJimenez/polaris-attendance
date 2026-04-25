<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DriverVerification;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $appTz = (string) config('app.timezone', 'UTC');
        $now = Carbon::now($appTz);
        $calendarMonthOffset = (int) $request->integer('calendar_month', 0);
        $calendarDate = $now->copy()->startOfMonth()->addMonths($calendarMonthOffset);
        $today = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $role = is_string(Auth::user()?->role) ? mb_strtolower(trim((string) Auth::user()?->role)) : '';

        $driverReminderClient = null;
        $driverFilterId = null;

        if ($role === 'driver') {
            $driver = User::query()->where('id', Auth::id())->where('role', 'driver')->first();
            $driverFilterId = $driver->id;
        }

        // Verification status for drivers
        $hasPendingVerification = false;
        $needsVerification = false;
        if ($role === 'driver') {
            $hasPendingVerification = DriverVerification::where('user_id', Auth::id())->where('status', 'pending')->exists();
            $hasApproved = DriverVerification::where('user_id', Auth::id())->where('status', 'approved')->exists();
            $needsVerification = ! $hasApproved && ! $hasPendingVerification;
        }

        $driverCount = $driverFilterId ? 1 : User::where('role', 'driver')->count();

        $todayCheckIns = Attendance::whereBetween('captured_at', [$today, $todayEnd])
            ->where('type', 'check_in')
            ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
            ->count();

        $todayCheckOuts = Attendance::whereBetween('captured_at', [$today, $todayEnd])
            ->where('type', 'check_out')
            ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
            ->count();

        $latestAttendance = Attendance::with('driver')
            ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
            ->latest('captured_at')
            ->limit(5)
            ->get();

        $recentActivity = Attendance::with('driver')
            ->latest('captured_at')
            ->limit(15)
            ->get();

        $driverDashboard = [
            'status' => null,
            'lastActivity' => null,
            'latestMapPoint' => null,
            'recentActivity' => [],
            'history' => [],
            'calendar' => [],
        ];

        if ($driverFilterId) {
            $todayCheckIn = Attendance::where('driver_id', $driverFilterId)
                ->whereBetween('captured_at', [$today, $todayEnd])
                ->where('type', 'check_in')
                ->latest('captured_at')
                ->first();

            $todayCheckOut = Attendance::where('driver_id', $driverFilterId)
                ->whereBetween('captured_at', [$today, $todayEnd])
                ->where('type', 'check_out')
                ->latest('captured_at')
                ->first();

            $statusLabel = 'Not checked in';
            if ($todayCheckOut) {
                $statusLabel = 'Checked out';
            } elseif ($todayCheckIn) {
                $statusLabel = 'Checked in';
            }

            $lastActivity = Attendance::where('driver_id', $driverFilterId)
                ->latest('captured_at')
                ->first();

            $historyRows = Attendance::where('driver_id', $driverFilterId)
                ->latest('captured_at')
                ->limit(500)
                ->get();

            $driverRecentActivity = Attendance::where('driver_id', $driverFilterId)
                ->latest('captured_at')
                ->limit(5)
                ->get();

            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
            $monthRows = Attendance::where('driver_id', $driverFilterId)
                ->whereBetween('captured_at', [$monthStart, $monthEnd])
                ->get();

            $calendarRows = $monthRows->groupBy(fn ($row) => $row->captured_at->format('Y-m-d'));
            $calendarDays = [];
            foreach ($calendarRows as $dateKey => $rows) {
                $hasCheckOut = $rows->contains(fn ($r) => $r->type === 'check_out');
                $hasCheckIn = $rows->contains(fn ($r) => $r->type === 'check_in');
                $calendarDays[$dateKey] = $hasCheckOut ? 'check_out' : ($hasCheckIn ? 'check_in' : null);
            }

            $driverDashboard = [
                'status' => [
                    'label' => $statusLabel,
                    'lastCheckInAt' => $todayCheckIn?->captured_at,
                    'lastCheckOutAt' => $todayCheckOut?->captured_at,
                ],
                'lastActivity' => $lastActivity,
                'latestMapPoint' => (function () use ($lastActivity) {
                    $meta = is_array($lastActivity?->meta) ? $lastActivity->meta : [];
                    $lat = isset($meta['latitude']) ? (float) $meta['latitude'] : null;
                    $lng = isset($meta['longitude']) ? (float) $meta['longitude'] : null;
                    if ($lat === null || $lng === null) {
                        return null;
                    }

                    return ['lat' => $lat, 'lng' => $lng];
                })(),
                'recentActivity' => $driverRecentActivity,
                'history' => $historyRows->map(fn ($row) => [
                    'type' => $row->type,
                    'captured_at' => $row->captured_at?->copy()->timezone($appTz)->toIso8601String(),
                    'captured_label' => $row->captured_at?->copy()->timezone($appTz)->format('M d, Y h:i A'),
                    'face_confidence' => $row->face_confidence,
                    'liveness_score' => $row->liveness_score,
                    'device_id' => $row->device_id,
                ])->values()->all(),
                'calendar' => [
                    'monthName' => $now->copy()->format('F Y'),
                    'daysInMonth' => $now->copy()->daysInMonth,
                    'firstDayOfWeek' => $now->copy()->startOfMonth()->dayOfWeek,
                    'days' => $calendarDays,
                ],
            ];

            $driverReminderClient = [
                'enabled' => (bool) Setting::get('driver_reminders_enabled', true),
                'adminReminders' => (bool) Setting::get('attendance_reminder_enabled', true),
                'showNotifications' => (bool) Setting::get('show_notifications', true),
                'sound' => (bool) Setting::get('driver_notification_sound', true),
                'repeat' => (bool) Setting::get('driver_reminder_repeat', true),
                'snoozeMin' => (int) Setting::get('driver_reminder_snooze', 5),
                'notifyCheckin' => (bool) Setting::get('notify_checkin_reminder', true),
                'notifyCheckout' => (bool) Setting::get('notify_checkout_reminder', true),
                'checkinTime' => (string) Setting::get('driver_checkin_reminder_time', '09:00'),
                'checkoutTime' => (string) Setting::get('driver_checkout_reminder_time', '17:00'),
                'beforeMin' => (int) Setting::get('driver_reminder_before_minutes', 15),
                'hasCheckedInToday' => $todayCheckIn !== null,
                'hasCheckedOutToday' => $todayCheckOut !== null,
            ];
        }

        // Chart data and summary for admin dashboard
        $chartData = [];
        $adminSummary = null;

        if ($role === 'admin' && !$driverFilterId) {
            // Last 7 days attendance trends
            $last7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $last7Days[] = $date->format('Y-m-d');
            }

            $weeklyTrends = Attendance::select(
                    DB::raw('DATE(captured_at) as date'),
                    DB::raw('COUNT(CASE WHEN type = "check_in" THEN 1 END) as check_ins'),
                    DB::raw('COUNT(CASE WHEN type = "check_out" THEN 1 END) as check_outs')
                )
                ->whereBetween('captured_at', [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()])
                ->groupBy(DB::raw('DATE(captured_at)'))
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $weeklyCheckIns = [];
            $weeklyCheckOuts = [];
            $weeklyLabels = [];

            foreach ($last7Days as $date) {
                $weeklyLabels[] = Carbon::parse($date)->format('M d');
                $data = $weeklyTrends->get($date);
                $weeklyCheckIns[] = $data ? (int)$data->check_ins : 0;
                $weeklyCheckOuts[] = $data ? (int)$data->check_outs : 0;
            }

            // Top 5 drivers by attendance (last 30 days)
            $topDrivers = Attendance::select(
                    'driver_id',
                    DB::raw('users.name as driver_name'),
                    DB::raw('COUNT(*) as attendance_count')
                )
                ->join('users', 'attendances.driver_id', '=', 'users.id')
                ->where('captured_at', '>=', $now->copy()->subDays(30)->startOfDay())
                ->groupBy('driver_id', 'users.name')
                ->orderByDesc('attendance_count')
                ->limit(5)
                ->get();

            $driverLabels = $topDrivers->pluck('driver_name')->toArray();
            $driverCounts = $topDrivers->pluck('attendance_count')->toArray();

            // This week vs last week comparison
            $thisWeekStart = $now->copy()->startOfWeek();
            $thisWeekEnd = $now->copy()->endOfWeek();
            $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
            $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();

            $thisWeekTotal = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])->count();
            $lastWeekTotal = Attendance::whereBetween('captured_at', [$lastWeekStart, $lastWeekEnd])->count();

            // Hourly distribution for today (process in PHP for database compatibility)
            $todayAttendances = Attendance::whereBetween('captured_at', [$today, $todayEnd])->get();

            $hourlyCheckIns = array_fill(0, 24, 0);
            $hourlyCheckOuts = array_fill(0, 24, 0);
            $hourlyLabels = [];

            foreach ($todayAttendances as $attendance) {
                $hour = (int)$attendance->captured_at->format('H');
                if ($attendance->type === 'check_in') {
                    $hourlyCheckIns[$hour]++;
                } else {
                    $hourlyCheckOuts[$hour]++;
                }
            }

            for ($h = 0; $h < 24; $h++) {
                $hourlyLabels[] = sprintf('%02d:00', $h);
            }

            // Status counts: Present / Late / Absent for Today, This Week and This Month
            $presentToday = Attendance::whereBetween('captured_at', [$today, $todayEnd])
                ->where('type', 'check_in')
                ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
                ->get()
                ->filter(fn ($a) => $a->status === 'Present')
                ->count();

            $lateToday = Attendance::whereBetween('captured_at', [$today, $todayEnd])
                ->where('type', 'check_in')
                ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
                ->get()
                ->filter(fn ($a) => $a->status === 'Late')
                ->count();

            $absentToday = User::where('role', 'driver')->when($driverFilterId, fn ($q) => $q->where('id', $driverFilterId))
                ->where('active', true)
                ->where('created_at', '<', $today->copy()->startOfDay())
                ->whereDoesntHave('attendances', fn ($q) => $q->whereBetween('captured_at', [$today, $todayEnd])->where('type', 'check_in'))
                ->count();

            $presentWeek = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])
                ->where('type', 'check_in')
                ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
                ->get()
                ->filter(fn ($a) => $a->status === 'Present')
                ->count();

            $lateWeek = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])
                ->where('type', 'check_in')
                ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
                ->get()
                ->filter(fn ($a) => $a->status === 'Late')
                ->count();

            $absentWeek = User::where('role', 'driver')->when($driverFilterId, fn ($q) => $q->where('id', $driverFilterId))
                ->where('active', true)
                ->where('created_at', '<', $thisWeekStart)
                ->whereDoesntHave('attendances', fn ($q) => $q->whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])->where('type', 'check_in'))
                ->count();

            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();

            $presentMonth = Attendance::whereBetween('captured_at', [$monthStart, $monthEnd])
                ->where('type', 'check_in')
                ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
                ->get()
                ->filter(fn ($a) => $a->status === 'Present')
                ->count();

            $lateMonth = Attendance::whereBetween('captured_at', [$monthStart, $monthEnd])
                ->where('type', 'check_in')
                ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
                ->get()
                ->filter(fn ($a) => $a->status === 'Late')
                ->count();

            $absentMonth = User::where('role', 'driver')->when($driverFilterId, fn ($q) => $q->where('id', $driverFilterId))
                ->where('active', true)
                ->where('created_at', '<', $monthStart)
                ->whereDoesntHave('attendances', fn ($q) => $q->whereBetween('captured_at', [$monthStart, $monthEnd])->where('type', 'check_in'))
                ->count();

            $weekCheckIns = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])
                ->where('type', 'check_in')
                ->count();
            $weekCheckOuts = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])
                ->where('type', 'check_out')
                ->count();
            $monthCheckIns = Attendance::whereBetween('captured_at', [$monthStart, $monthEnd])
                ->where('type', 'check_in')
                ->count();
            $monthCheckOuts = Attendance::whereBetween('captured_at', [$monthStart, $monthEnd])
                ->where('type', 'check_out')
                ->count();

            $adminSummary = [
                'today' => [
                    'check_ins' => $todayCheckIns,
                    'check_outs' => $todayCheckOuts,
                    'present' => $presentToday,
                    'late' => $lateToday,
                    'absent' => $absentToday,
                ],
                'week' => [
                    'check_ins' => $weekCheckIns,
                    'check_outs' => $weekCheckOuts,
                    'present' => $presentWeek,
                    'late' => $lateWeek,
                    'absent' => $absentWeek,
                ],
                'month' => [
                    'check_ins' => $monthCheckIns,
                    'check_outs' => $monthCheckOuts,
                    'present' => $presentMonth,
                    'late' => $lateMonth,
                    'absent' => $absentMonth,
                ],
            ];

            // Status by date (this week) for Status Breakdown chart
            $weekDates = [];
            $statusByDatePresent = [];
            $statusByDateLate = [];
            $statusByDateAbsent = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $thisWeekStart->copy()->addDays($i);
                $weekDates[] = $date->format('D j'); // e.g. "Mon 2"
                $eligibleDriverCount = User::where('role', 'driver')
                    ->where('active', true)
                    ->where('created_at', '<', $date->copy()->startOfDay())
                    ->count();
                $dayCheckIns = Attendance::whereBetween('captured_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                    ->where('type', 'check_in')
                    ->get();
                $present = $dayCheckIns->filter(fn ($a) => $a->status === 'Present')->count();
                $late = $dayCheckIns->filter(fn ($a) => $a->status === 'Late')->count();
                $driversCheckedIn = $dayCheckIns->pluck('driver_id')->unique()->count();
                $statusByDatePresent[] = $present;
                $statusByDateLate[] = $late;
                $statusByDateAbsent[] = max(0, $eligibleDriverCount - $driversCheckedIn);
            }

            $chartData = [
                'weeklyLabels' => $weeklyLabels,
                'weeklyCheckIns' => $weeklyCheckIns,
                'weeklyCheckOuts' => $weeklyCheckOuts,
                'driverLabels' => $driverLabels,
                'driverCounts' => $driverCounts,
                'thisWeekTotal' => $thisWeekTotal,
                'lastWeekTotal' => $lastWeekTotal,
                'hourlyLabels' => $hourlyLabels,
                'hourlyCheckIns' => $hourlyCheckIns,
                'hourlyCheckOuts' => $hourlyCheckOuts,
                'statusLabels' => ['Today', 'This Week', 'This Month'],
                'presentCounts' => [$presentToday, $presentWeek, $presentMonth],
                'lateCounts' => [$lateToday, $lateWeek, $lateMonth],
                'absentCounts' => [$absentToday, $absentWeek, $absentMonth],
                'statusByDateLabels' => $weekDates,
                'statusByDatePresent' => $statusByDatePresent,
                'statusByDateLate' => $statusByDateLate,
                'statusByDateAbsent' => $statusByDateAbsent,
            ];
        }

        return view('dashboard', [
            'driverCount' => $driverCount,
            'todayCheckIns' => $todayCheckIns,
            'todayCheckOuts' => $todayCheckOuts,
            'latestAttendance' => $latestAttendance,
            'recentActivity' => $recentActivity,
            'chartData' => $chartData,
            'adminSummary' => $adminSummary,
            'hasPendingVerification' => $hasPendingVerification ?? false,
            'needsVerification' => $needsVerification ?? false,
            'driverSelfId' => $driverFilterId,
            'driverDashboard' => $driverDashboard,
            'driverReminderClient' => $driverReminderClient,
            'driverLocationSharingEnabled' => (bool) (Auth::user()?->location_sharing_enabled ?? false),
            'adminCalendarDate' => $calendarDate,
            'adminCalendarMonthOffset' => $calendarMonthOffset,
        ]);
    }
}

