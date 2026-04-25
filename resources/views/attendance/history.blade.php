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

    @php
        $calendarYear = now()->year;
        $calendarMonth = now()->month;
        $calendarFirst = \Carbon\Carbon::create($calendarYear, $calendarMonth, 1);
        $calendarMap = [];
        foreach ($groupedHistory as $rowsByBucket) {
            foreach ($rowsByBucket as $row) {
                $d = $row->captured_at?->format('Y-m-d');
                if (! $d || isset($calendarMap[$d])) {
                    continue;
                }
                if ($row->type === 'check_in') {
                    $st = mb_strtolower(trim((string) ($row->status ?? 'present')));
                    $calendarMap[$d] = in_array($st, ['present', 'late', 'absent'], true) ? $st : 'present';
                }
            }
        }
    @endphp
    <div class="glass p-4 sm:p-5 overflow-hidden">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3">Calendar ({{ $calendarFirst->format('F Y') }})</h3>
        <div class="mx-auto grid max-w-xl grid-cols-7 gap-1 text-center w-full min-w-0">
            @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                <span class="text-slate-400 font-medium text-[10px] sm:text-xs py-1">{{ $d }}</span>
            @endforeach
            @for($i = 0; $i < $calendarFirst->dayOfWeek; $i++)
                <span class="min-w-0 aspect-square" aria-hidden="true"></span>
            @endfor
            @for($day=1; $day <= $calendarFirst->daysInMonth; $day++)
                @php
                    $dateKey = sprintf('%04d-%02d-%02d', $calendarYear, $calendarMonth, $day);
                    $status = $calendarMap[$dateKey] ?? null;
                    $wrap = 'border border-white/15 bg-transparent text-slate-400';
                    if ($status === 'present') $wrap = 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-200/90';
                    if ($status === 'late') $wrap = 'border border-amber-500/35 bg-amber-500/10 text-amber-200/90';
                    if ($status === 'absent') $wrap = 'border border-rose-500/30 bg-rose-500/10 text-rose-200/90';
                @endphp
                <span class="min-w-0 aspect-square max-w-full flex items-center justify-center rounded-md sm:rounded-lg text-[10px] sm:text-[11px] font-medium leading-tight py-0.5 {{ $wrap }}">{{ $day }}</span>
            @endfor
        </div>
    </div>

    @php
        $latestMapPoint = null;
        foreach ($groupedHistory as $bucketRows) {
            foreach ($bucketRows as $row) {
                $meta = is_array($row->meta ?? null) ? $row->meta : [];
                if (is_numeric(data_get($meta, 'latitude')) && is_numeric(data_get($meta, 'longitude'))) {
                    $latestMapPoint = ['lat' => (float) data_get($meta, 'latitude'), 'lng' => (float) data_get($meta, 'longitude')];
                    break 2;
                }
            }
        }
    @endphp
    <div class="glass p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-white mb-3">Latest Check-in/Out Map</h2>
        @if($latestMapPoint)
            <iframe
                title="Driver attendance map"
                src="https://maps.google.com/maps?q={{ $latestMapPoint['lat'] }},{{ $latestMapPoint['lng'] }}&z=16&output=embed"
                class="w-full h-72 rounded-xl border border-white/10"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        @else
            <p class="text-slate-400 text-sm">No location data found yet.</p>
        @endif
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

