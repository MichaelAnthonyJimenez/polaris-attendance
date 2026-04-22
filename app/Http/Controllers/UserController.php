<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function show(User $user): View
    {
        $recentAttendances = collect();
        if (($user->role ?? '') === 'driver') {
            $recentAttendances = $user->attendances()
                ->latest('captured_at')
                ->limit(20)
                ->get();
        }

        return view('users.show', [
            'user' => $user,
            'recentAttendances' => $recentAttendances,
        ]);
    }

    public function attendanceHistory(Request $request, User $user): View
    {
        if (($user->role ?? '') !== 'driver') {
            abort(404);
        }

        $period = (string) $request->query('period', 'daily');
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'daily';
        }

        $rows = Attendance::query()
            ->where('driver_id', $user->id)
            ->orderByDesc('captured_at')
            ->limit(800)
            ->get();

        $grouped = match ($period) {
            'weekly' => $rows->groupBy(fn (Attendance $row) => $row->captured_at?->copy()->startOfWeek()->format('Y-m-d') ?? 'Unknown'),
            'monthly' => $rows->groupBy(fn (Attendance $row) => $row->captured_at?->format('Y-m') ?? 'Unknown'),
            default => $rows->groupBy(fn (Attendance $row) => $row->captured_at?->format('Y-m-d') ?? 'Unknown'),
        };

        $calendarAnchor = now()->startOfMonth();
        $monthStart = $calendarAnchor->copy()->startOfMonth();
        $monthEnd = $calendarAnchor->copy()->endOfMonth();
        $monthRows = Attendance::query()
            ->where('driver_id', $user->id)
            ->whereBetween('captured_at', [$monthStart, $monthEnd])
            ->get();
        $calendarRows = $monthRows->groupBy(fn ($row) => $row->captured_at->format('Y-m-d'));
        $calendarDays = [];
        for ($d = 1; $d <= $monthStart->daysInMonth; $d++) {
            $day = $monthStart->copy()->day($d)->format('Y-m-d');
            $checkIn = collect($calendarRows->get($day, []))->first(fn ($r) => $r->type === 'check_in');
            if (! $checkIn) {
                $calendarDays[$day] = null;
                continue;
            }
            $status = mb_strtolower(trim((string) ($checkIn->status ?? 'present')));
            $calendarDays[$day] = in_array($status, ['late', 'absent', 'present'], true) ? $status : 'present';
        }

        return view('users.attendance-history', [
            'driver' => $user,
            'period' => $period,
            'groupedHistory' => $grouped,
            'calendar' => [
                'year' => (int) $monthStart->format('Y'),
                'month' => (int) $monthStart->format('n'),
                'monthName' => $monthStart->format('F Y'),
                'daysInMonth' => $monthStart->daysInMonth,
                'firstDayOfWeek' => $monthStart->dayOfWeek,
                'days' => $calendarDays,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,driver'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        AuditLogger::log('created', 'User', $user->id, null, ['name' => $user->name, 'email' => $user->email, 'role' => $user->role], "User {$user->name} created with role {$user->role}");

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,driver'],
        ]);

        $oldValues = $user->only(['name', 'email', 'role']);
        
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        AuditLogger::log('updated', 'User', $user->id, $oldValues, $user->only(['name', 'email', 'role']), "User {$user->name} updated (role changed to {$user->role})");

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $userName = $user->name;
        $userId = $user->id;
        
        $user->delete();

        AuditLogger::log('deleted', 'User', $userId, ['name' => $userName], null, "User {$userName} deleted");

        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }
}
