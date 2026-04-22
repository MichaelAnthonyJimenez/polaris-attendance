<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
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
