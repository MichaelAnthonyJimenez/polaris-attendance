<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        try {
            $driverCount = User::where('role', 'driver')->count();
            $checkInsTodayCount = Attendance::where('type', 'check_in')
                ->whereDate('captured_at', today())
                ->count();
            $pendingSyncCount = Attendance::where('synced', false)->count();
        } catch (QueryException) {
            // Allow the landing page to render even if DB is not ready.
            $driverCount = 0;
            $checkInsTodayCount = 0;
            $pendingSyncCount = 0;
        }

        return view('home', [
            'driverCount' => $driverCount,
            'checkInsTodayCount' => $checkInsTodayCount,
            'pendingSyncCount' => $pendingSyncCount,
        ]);
    }
}
