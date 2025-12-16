<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();

        $driverFilterId = null;

        if (Auth::user()?->role === 'driver') {
            // Ensure we have a driver mapped to this user
            $driver = Driver::firstOrCreate(
                ['badge_number' => 'user-'.Auth::id()],
                ['name' => Auth::user()->name ?? 'Driver '.Auth::id(), 'active' => true]
            );
            $driverFilterId = $driver->id;
        }

        $driverCount = $driverFilterId ? 1 : Driver::count();

        $todayCheckIns = Attendance::whereDate('captured_at', $today)
            ->where('type', 'check_in')
            ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
            ->count();

        $todayCheckOuts = Attendance::whereDate('captured_at', $today)
            ->where('type', 'check_out')
            ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
            ->count();

        $latestAttendance = Attendance::with('driver')
            ->when($driverFilterId, fn ($q) => $q->where('driver_id', $driverFilterId))
            ->latest('captured_at')
            ->limit(5)
            ->get();

        // Chart data for admin dashboard
        $chartData = [];
        
        if (Auth::user()?->role === 'admin' && !$driverFilterId) {
            // Last 7 days attendance trends
            $last7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $last7Days[] = $date->format('Y-m-d');
            }

            $weeklyTrends = Attendance::select(
                    DB::raw('DATE(captured_at) as date'),
                    DB::raw('COUNT(CASE WHEN type = "check_in" THEN 1 END) as check_ins'),
                    DB::raw('COUNT(CASE WHEN type = "check_out" THEN 1 END) as check_outs')
                )
                ->whereBetween('captured_at', [Carbon::today()->subDays(6)->startOfDay(), Carbon::today()->endOfDay()])
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
                    DB::raw('drivers.name as driver_name'),
                    DB::raw('COUNT(*) as attendance_count')
                )
                ->join('drivers', 'attendances.driver_id', '=', 'drivers.id')
                ->where('captured_at', '>=', Carbon::today()->subDays(30))
                ->groupBy('driver_id', 'drivers.name')
                ->orderByDesc('attendance_count')
                ->limit(5)
                ->get();

            $driverLabels = $topDrivers->pluck('driver_name')->toArray();
            $driverCounts = $topDrivers->pluck('attendance_count')->toArray();

            // This week vs last week comparison
            $thisWeekStart = Carbon::today()->startOfWeek();
            $thisWeekEnd = Carbon::today()->endOfWeek();
            $lastWeekStart = Carbon::today()->subWeek()->startOfWeek();
            $lastWeekEnd = Carbon::today()->subWeek()->endOfWeek();

            $thisWeekTotal = Attendance::whereBetween('captured_at', [$thisWeekStart, $thisWeekEnd])->count();
            $lastWeekTotal = Attendance::whereBetween('captured_at', [$lastWeekStart, $lastWeekEnd])->count();

            // Hourly distribution for today (process in PHP for database compatibility)
            $todayAttendances = Attendance::whereDate('captured_at', $today)->get();
            
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
            ];
        }

        return view('dashboard', [
            'driverCount' => $driverCount,
            'todayCheckIns' => $todayCheckIns,
            'todayCheckOuts' => $todayCheckOuts,
            'latestAttendance' => $latestAttendance,
            'chartData' => $chartData,
        ]);
    }
}

