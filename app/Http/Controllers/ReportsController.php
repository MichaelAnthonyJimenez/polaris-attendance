<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $driverId = $request->input('driver_id');

        $query = Attendance::with('driver')
            ->whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59']);

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        $attendances = $query->orderBy('captured_at', 'desc')->get();

        // Statistics
        $stats = [
            'total_check_ins' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('type', 'check_in')
                ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
                ->count(),
            'total_check_outs' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('type', 'check_out')
                ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
                ->count(),
            'avg_face_confidence' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
                ->whereNotNull('face_confidence')
                ->avg('face_confidence'),
            'avg_liveness_score' => Attendance::whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
                ->whereNotNull('liveness_score')
                ->avg('liveness_score'),
        ];

        // Daily attendance chart data
        $dailyData = Attendance::select(
                DB::raw('DATE(captured_at) as date'),
                DB::raw('COUNT(CASE WHEN type = "check_in" THEN 1 END) as check_ins'),
                DB::raw('COUNT(CASE WHEN type = "check_out" THEN 1 END) as check_outs')
            )
            ->whereBetween('captured_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.index', [
            'attendances' => $attendances,
            'stats' => $stats,
            'dailyData' => $dailyData,
            'drivers' => Driver::where('active', true)->orderBy('name')->get(),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'driver_id' => $driverId,
            ],
        ]);
    }
}
