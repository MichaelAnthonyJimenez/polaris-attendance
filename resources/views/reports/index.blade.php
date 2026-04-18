@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <h1 class="text-2xl sm:text-3xl font-bold text-white">Reports</h1>
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
        <!-- Mobile list -->
        <div class="space-y-3 md:hidden">
            @forelse($attendances as $attendance)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm text-slate-200 font-medium truncate">
                                {{ $attendance->driver->name ?? 'Unknown' }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $attendance->captured_at?->format('M d, Y H:i') }}
                            </div>
                        </div>
                        <span class="shrink-0 px-2 py-1 rounded text-xs {{ $attendance->type === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                            {{ str_replace('_', ' ', $attendance->type) }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-2 text-sm">
                        <div class="text-slate-300">
                            <span class="text-slate-500">Face match:</span>
                            @if($attendance->face_confidence)
                                <span class="text-emerald-300">{{ $attendance->face_confidence }}%</span>
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </div>
                        <div class="text-slate-300">
                            <span class="text-slate-500">Liveness:</span>
                            @if($attendance->liveness_score)
                                <span class="text-emerald-300">{{ number_format($attendance->liveness_score, 2) }}</span>
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </div>
                        <div class="text-slate-300">
                            <span class="text-slate-500">Device:</span>
                            <span class="text-slate-200 break-words">{{ $attendance->device_id ?? '—' }}</span>
                        </div>
                        <div class="pt-1">
                            <a href="{{ route('attendance.show', $attendance) }}" class="btn-secondary inline-flex items-center text-xs px-3 py-1.5">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400">No attendance records found.</div>
            @endforelse
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Date & Time</th>
                        <th>Face Match</th>
                        <th>Liveness</th>
                        <th>Device</th>
                        <th>Action</th>
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
                            <td>
                                <a href="{{ route('attendance.show', $attendance) }}" class="btn-secondary inline-flex items-center text-xs px-3 py-1.5">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="GET" action="{{ route('reports.export') }}" class="mt-4 flex flex-col sm:flex-row sm:items-end sm:justify-end gap-3">
            <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
            <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
            <input type="hidden" name="driver_id" value="{{ $filters['driver_id'] }}">
            <div>
                <label for="attendance-export-as" class="form-label">Export As</label>
                <select id="attendance-export-as" name="export_as" class="form-select min-w-[200px]">
                    <option value="csv">CSV</option>
                    <option value="word">Word</option>
                    <option value="excel">Excel</option>
                    <option value="pdf">PDF</option>
                </select>
            </div>
            <button type="submit" class="btn-primary px-4 py-2 text-sm leading-tight sm:w-auto self-start sm:self-auto">Export</button>
        </form>
    </div>
</div>
@endsection

