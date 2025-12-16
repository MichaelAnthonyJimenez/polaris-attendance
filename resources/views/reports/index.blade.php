@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-white">Reports</h1>
    </div>

    <!-- Filters -->
    <div class="glass p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Filters</h2>
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Driver</label>
                <select name="driver_id" class="form-select">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ $filters['driver_id'] == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass p-6">
            <div class="text-sm text-slate-300 mb-1">Total Check-ins</div>
            <div class="text-3xl font-bold text-white">{{ $stats['total_check_ins'] }}</div>
        </div>
        <div class="glass p-6">
            <div class="text-sm text-slate-300 mb-1">Total Check-outs</div>
            <div class="text-3xl font-bold text-white">{{ $stats['total_check_outs'] }}</div>
        </div>
        <div class="glass p-6">
            <div class="text-sm text-slate-300 mb-1">Avg Face Confidence</div>
            <div class="text-3xl font-bold text-white">{{ $stats['avg_face_confidence'] ? number_format($stats['avg_face_confidence'], 1) . '%' : 'N/A' }}</div>
        </div>
        <div class="glass p-6">
            <div class="text-sm text-slate-300 mb-1">Avg Liveness Score</div>
            <div class="text-3xl font-bold text-white">{{ $stats['avg_liveness_score'] ? number_format($stats['avg_liveness_score'], 2) : 'N/A' }}</div>
        </div>
    </div>

    <!-- Attendance List -->
    <div class="glass p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Attendance Records</h2>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Date & Time</th>
                        <th>Face Match</th>
                        <th>Liveness</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="font-medium">{{ $attendance->driver->name ?? 'Unknown' }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-xs {{ $attendance->type === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                                    {{ str_replace('_', ' ', $attendance->type) }}
                                </span>
                            </td>
                            <td>{{ $attendance->captured_at?->format('M d, Y H:i') }}</td>
                            <td>
                                @if($attendance->face_confidence)
                                    <span class="text-emerald-300">{{ $attendance->face_confidence }}%</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->liveness_score)
                                    <span class="text-emerald-300">{{ number_format($attendance->liveness_score, 2) }}</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="text-slate-300">{{ $attendance->device_id ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

