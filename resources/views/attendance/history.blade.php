@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="glass p-5 sm:p-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Attendance History</h1>
            <p class="text-sm text-slate-300 mt-1">Review your daily, weekly, or monthly records.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('attendance.history', ['period' => 'daily']) }}" class="btn-secondary text-xs {{ $period === 'daily' ? 'bg-blue-500/30' : '' }}">Daily</a>
            <a href="{{ route('attendance.history', ['period' => 'weekly']) }}" class="btn-secondary text-xs {{ $period === 'weekly' ? 'bg-blue-500/30' : '' }}">Weekly</a>
            <a href="{{ route('attendance.history', ['period' => 'monthly']) }}" class="btn-secondary text-xs {{ $period === 'monthly' ? 'bg-blue-500/30' : '' }}">Monthly</a>
        </div>
    </div>

    @forelse($groupedHistory as $bucket => $rows)
        <div class="glass p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-white mb-3">{{ $bucket }}</h2>
            <div class="overflow-x-auto">
                <table class="table-glass min-w-[720px] w-full">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Captured At</th>
                            <th>Total Hours</th>
                            <th>Face</th>
                            <th>Liveness</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php
                                $meta = is_array($row->meta ?? null) ? $row->meta : [];
                                $lat = data_get($meta, 'latitude');
                                $lng = data_get($meta, 'longitude');
                            @endphp
                            <tr>
                                <td>{{ str_replace('_', ' ', $row->type) }}</td>
                                <td>{{ $row->captured_at?->format('M d, Y H:i') ?? '—' }}</td>
                                <td>{{ $row->type === 'check_out' && $row->total_hours !== null ? number_format((float) $row->total_hours, 2) . ' h' : '—' }}</td>
                                <td>{{ $row->face_confidence ? $row->face_confidence . '%' : '—' }}</td>
                                <td>{{ $row->liveness_score ? number_format((float) $row->liveness_score, 2) : '—' }}</td>
                                <td>
                                    @if(is_numeric($lat) && is_numeric($lng))
                                        <a href="https://www.google.com/maps?q={{ (float) $lat }},{{ (float) $lng }}" class="text-blue-400 hover:text-blue-300" target="_blank" rel="noopener">View Map</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="glass p-6 text-slate-400 text-center">No attendance history found.</div>
    @endforelse
</div>
@endsection

