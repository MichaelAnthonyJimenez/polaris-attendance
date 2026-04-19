<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'driverCount' => User::where('role', 'driver')->count(),
            'checkInsTodayCount' => Attendance::where('type', 'check_in')
                ->whereDate('captured_at', today())
                ->count(),
            'pendingSyncCount' => Attendance::where('synced', false)->count(),
        ]);
    }
}
