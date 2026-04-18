<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $sort = $request->input('sort');

        $usersQuery = User::query();

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'name_asc':
                $usersQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $usersQuery->orderBy('name', 'desc');
                break;
            case 'status_verified':
                $usersQuery->whereNotNull('email_verified_at')->orderBy('created_at', 'desc');
                break;
            case 'status_unverified':
                $usersQuery->whereNull('email_verified_at')->orderBy('created_at', 'desc');
                break;
            case 'role_admin':
                $usersQuery->where('role', 'admin')->orderBy('created_at', 'desc');
                break;
            case 'role_driver':
                $usersQuery->where('role', 'driver')->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $usersQuery->orderBy('created_at', 'asc');
                break;
            case 'latest':
            default:
                $usersQuery->orderBy('created_at', 'desc');
                break;
        }

        return view('users.index', [
            'users' => $usersQuery->paginate(15)->withQueryString(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $badgeRules = ['nullable', 'string', 'max:50'];
        if ($request->input('role') === 'driver' && trim((string) $request->input('badge_number', '')) !== '') {
            $badgeRules[] = 'unique:users,badge_number';
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,driver'],
            'badge_number' => $badgeRules,
        ]);

        $badgeNumber = null;
        if ($data['role'] === 'driver') {
            $badgeNumber = trim((string) ($data['badge_number'] ?? '')) !== ''
                ? $data['badge_number']
                : User::nextDriverBadgeNumber();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'badge_number' => $badgeNumber,
        ]);

        AuditLogger::log('created', 'User', $user->id, null, ['name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'badge_number' => $user->badge_number], "User {$user->name} created with role {$user->role}");

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
        ]);
    }

    public function show(User $user): View
    {
        return view('users.show', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $badgeRules = ['nullable', 'string', 'max:50'];
        if ($request->input('role') === 'driver' && trim((string) $request->input('badge_number', '')) !== '') {
            $badgeRules[] = Rule::unique('users', 'badge_number')->ignore($user->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,driver'],
            'badge_number' => $badgeRules,
        ]);

        $oldValues = $user->only(['name', 'email', 'role', 'badge_number']);

        $badgeNumber = null;
        if ($validated['role'] === 'driver') {
            $trimmed = trim((string) ($validated['badge_number'] ?? ''));
            if ($trimmed !== '') {
                $badgeNumber = $trimmed;
            } elseif (($user->role ?? null) === 'driver' && $user->badge_number) {
                $badgeNumber = $user->badge_number;
            } else {
                $badgeNumber = User::nextDriverBadgeNumber();
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'badge_number' => $badgeNumber,
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        AuditLogger::log('updated', 'User', $user->id, $oldValues, $user->only(['name', 'email', 'role', 'badge_number']), "User {$user->name} updated (role changed to {$user->role})");

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
