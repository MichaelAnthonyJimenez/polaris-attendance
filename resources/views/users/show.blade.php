@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="glass p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="min-w-0 flex items-center gap-3">
                <span class="flex items-center justify-center w-14 h-14 rounded-full overflow-hidden bg-slate-600 text-white font-medium text-base border border-white/10 shrink-0">
                    @if($user->profile_photo_url ?? null)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        {{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}
                    @endif
                </span>
                <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight">{{ $user->name }}</h1>
                <p class="text-slate-400 mt-1 text-sm sm:text-base">User details</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                <a href="{{ route('users.edit', $user) }}" class="btn-primary text-center">Edit User</a>
                <a href="{{ route('users.index') }}" class="btn-secondary text-center">Back to Users</a>
            </div>
        </div>
    </div>

    <div class="glass p-5 sm:p-6">
        <h2 class="text-lg sm:text-xl font-semibold text-white mb-4">Account Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-slate-400">Name</label>
                <p class="text-white mt-1 break-words">{{ $user->name }}</p>
            </div>
            <div>
                <label class="text-sm text-slate-400">Email</label>
                <p class="text-white mt-1 break-all">{{ $user->email }}</p>
            </div>
            <div>
                <label class="text-sm text-slate-400">Role</label>
                <p class="mt-1">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-200 border border-purple-500/40' : 'bg-blue-500/20 text-blue-200 border border-blue-500/40' }}">
                        {{ ucfirst($user->role ?? 'driver') }}
                    </span>
                </p>
            </div>
            <div>
                <label class="text-sm text-slate-400">Badge Number</label>
                <p class="text-white mt-1">{{ $user->badge_number ?: '—' }}</p>
            </div>
            <div>
                <label class="text-sm text-slate-400">Verification Status</label>
                <p class="mt-1">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $user->email_verified_at ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-500/40' : 'bg-slate-700/40 text-slate-200 border border-slate-600/60' }}">
                        {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                    </span>
                </p>
            </div>
            <div>
                <label class="text-sm text-slate-400">Created At</label>
                <p class="text-white mt-1">{{ $user->created_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm text-slate-400">Last Updated</label>
                <p class="text-white mt-1">{{ $user->updated_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

@if(($user->role ?? '') === 'driver')
<div class="max-w-3xl mx-auto mt-6 glass p-5 sm:p-6">
    <div class="flex items-center justify-between gap-3 mb-4">
        <h2 class="text-lg sm:text-xl font-semibold text-white">Attendance History</h2>
        <a href="{{ route('users.attendance-history', $user) }}" class="btn-secondary text-xs">See Attendance</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table-glass min-w-[560px] w-full">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Captured At</th>
                    <th>Total Hours</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAttendances as $attendance)
                    @php
                        $meta = is_array($attendance->meta ?? null) ? $attendance->meta : [];
                        $lat = data_get($meta, 'latitude');
                        $lng = data_get($meta, 'longitude');
                    @endphp
                    <tr>
                        <td>{{ str_replace('_', ' ', $attendance->type) }}</td>
                        <td>{{ $attendance->captured_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td>{{ $attendance->type === 'check_out' && $attendance->total_hours !== null ? number_format((float) $attendance->total_hours, 2) . ' h' : '—' }}</td>
                        <td>
                            @if(is_numeric($lat) && is_numeric($lng))
                                <a href="https://www.google.com/maps?q={{ (float) $lat }},{{ (float) $lng }}" target="_blank" rel="noopener" class="text-blue-400 hover:text-blue-300">Map</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-400 py-6">No attendance records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
