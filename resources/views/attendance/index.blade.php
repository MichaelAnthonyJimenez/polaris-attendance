@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @php
        $role = mb_strtolower(trim((string) (auth()->user()?->role ?? '')));
    @endphp
    @if($role === 'admin' && !empty($adminChartData))
        <div class="grid grid-cols-3 gap-2 sm:gap-3 md:gap-4">
            <div class="glass p-3 sm:p-4 md:p-5 min-w-0">
                <div class="text-[10px] sm:text-xs md:text-sm text-slate-300 mb-1 truncate">Today</div>
                <div class="flex flex-col gap-0.5 sm:flex-row sm:items-baseline sm:gap-2">
                    <span class="text-xl sm:text-2xl md:text-3xl font-bold text-emerald-300 tabular-nums">{{ $adminChartData['today']['present'] }}</span>
                    <span class="text-[10px] sm:text-xs text-slate-300 uppercase tracking-wide">Present</span>
                </div>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-1 leading-snug break-words">Late: {{ $adminChartData['today']['late'] }} · Absent: {{ $adminChartData['today']['absent'] }}</p>
            </div>
            <div class="glass p-3 sm:p-4 md:p-5 min-w-0">
                <div class="text-[10px] sm:text-xs md:text-sm text-slate-300 mb-1 truncate">This Week</div>
                <div class="flex flex-col gap-0.5 sm:flex-row sm:items-baseline sm:gap-2">
                    <span class="text-xl sm:text-2xl md:text-3xl font-bold text-emerald-300 tabular-nums">{{ $adminChartData['week']['present'] }}</span>
                    <span class="text-[10px] sm:text-xs text-slate-300 uppercase tracking-wide">Present</span>
                </div>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-1 leading-snug break-words">Late: {{ $adminChartData['week']['late'] }} · Absent: {{ $adminChartData['week']['absent'] }}</p>
            </div>
            <div class="glass p-3 sm:p-4 md:p-5 min-w-0">
                <div class="text-[10px] sm:text-xs md:text-sm text-slate-300 mb-1 truncate">This Month</div>
                <div class="flex flex-col gap-0.5 sm:flex-row sm:items-baseline sm:gap-2">
                    <span class="text-xl sm:text-2xl md:text-3xl font-bold text-emerald-300 tabular-nums">{{ $adminChartData['month']['present'] }}</span>
                    <span class="text-[10px] sm:text-xs text-slate-300 uppercase tracking-wide">Present</span>
                </div>
                <p class="text-[10px] sm:text-xs text-slate-400 mt-1 leading-snug break-words">Late: {{ $adminChartData['month']['late'] }} · Absent: {{ $adminChartData['month']['absent'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="glass p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Today's Status</h2>
                <p class="text-xs text-slate-300 mb-3">Present, Late & Absent</p>
                <div class="h-64">
                    <canvas id="attendanceTodayPieChart"></canvas>
                </div>
            </div>
            <div class="glass p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Status Breakdown</h2>
                <div class="h-64">
                    <canvas id="attendanceStatusChart"></canvas>
                </div>
            </div>
        </div>
    @endif

    @if($role === 'driver')
        @php
            $driverCal = data_get($driverHistoryData, 'calendar', []);
            $driverCalPrev = data_get($driverCal, 'prevMonth');
            $driverCalNext = data_get($driverCal, 'nextMonth');
        @endphp
        <div class="glass p-4 sm:p-5 md:p-5 overflow-hidden">
            <div class="flex items-center justify-between gap-2 mb-3 min-w-0">
                <h3 class="text-base sm:text-lg font-semibold text-white shrink-0">Calendar</h3>
                <div class="flex items-center gap-1 sm:gap-2 min-w-0 flex-1 justify-end">
                    <a
                        href="{{ route('attendance.index', ['history_month' => $driverCalPrev]) }}"
                        class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-lg border border-white/25 text-slate-200 hover:bg-white/10 hover:border-white/40 transition shrink-0"
                        title="Previous month"
                        aria-label="Previous month"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <span class="text-[11px] sm:text-xs text-slate-300 text-center truncate font-medium tabular-nums px-1">{{ data_get($driverHistoryData, 'calendar.monthName') }}</span>
                    @if($driverCalNext)
                        <a
                            href="{{ route('attendance.index', ['history_month' => $driverCalNext]) }}"
                            class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-lg border border-white/25 text-slate-200 hover:bg-white/10 hover:border-white/40 transition shrink-0"
                            title="Next month"
                            aria-label="Next month"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-lg border border-white/10 text-slate-600 shrink-0 cursor-not-allowed" aria-hidden="true" title="Current month">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
            <div id="driverCalendar" class="mx-auto grid max-w-xl grid-cols-7 gap-0.5 sm:gap-1 text-center w-full min-w-0"></div>
            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 mt-3 text-[10px] sm:text-xs text-slate-400">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-green-500 shrink-0 border border-green-400/80" title="Present"></span> Present</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-yellow-400 shrink-0 border border-yellow-300/90" title="Late"></span> Late</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-red-500 shrink-0 border border-red-400/80" title="Absent"></span> Absent</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-transparent shrink-0 border border-white/25" title="No color"></span> Pending / no record</span>
            </div>
        </div>

        <div class="glass p-4 sm:p-6 md:p-7">
            <div class="flex flex-wrap gap-1.5 sm:gap-2 mb-3 sm:mb-4">
                <button type="button" class="btn-secondary px-3 py-1.5 sm:px-5 sm:py-2.5 text-[11px] sm:text-xs history-filter-btn" data-filter="all">All</button>
                <button type="button" class="btn-secondary px-3 py-1.5 sm:px-5 sm:py-2.5 text-[11px] sm:text-xs history-filter-btn" data-filter="daily">Daily</button>
                <button type="button" class="btn-secondary px-3 py-1.5 sm:px-5 sm:py-2.5 text-[11px] sm:text-xs history-filter-btn" data-filter="weekly">Weekly</button>
                <button type="button" class="btn-secondary px-3 py-1.5 sm:px-5 sm:py-2.5 text-[11px] sm:text-xs history-filter-btn" data-filter="monthly">Monthly</button>
                <button type="button" class="btn-secondary px-3 py-1.5 sm:px-5 sm:py-2.5 text-[11px] sm:text-xs history-filter-btn" data-filter="yearly">Yearly</button>
            </div>
            <h2 class="text-lg sm:text-xl font-bold text-white mb-3 sm:mb-4">Attendance History</h2>
            <div class="overflow-x-auto -mx-1 px-1 sm:mx-0 sm:px-0">
                <table class="table-glass text-xs sm:text-sm min-w-[320px] w-full">
                    <thead>
                        <tr>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap">Type</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap">Captured At</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap">Hours</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap hidden sm:table-cell">Face</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap hidden sm:table-cell">Liveness</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 min-w-0">Device</th>
                        </tr>
                    </thead>
                    <tbody id="driverHistoryTableBody"></tbody>
                </table>
            </div>
        </div>
    @endif

    @if($role !== 'driver')
        <div class="glass p-4 sm:p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-white">Attendance Records</h2>
                <a href="{{ route('reports.index') }}" class="btn-secondary text-xs px-3 py-2">Reports</a>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse ($attendances as $row)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3 sm:p-4" id="attendance-card-{{ $row->id }}">
                        <div class="flex items-start justify-between gap-2 min-w-0">
                            <div class="font-medium text-white truncate min-w-0">{{ $row->driver->name ?? 'Unknown' }}</div>
                            <span class="px-2 py-1 rounded text-xs shrink-0 {{ $row->type === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                                {{ str_replace('_', ' ', $row->type) }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-slate-300">{{ $row->captured_at?->format('M d, Y · H:i') }}</div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('attendance.show', $row) }}" class="btn-secondary text-xs px-3 py-2">View</a>
                            @if(!empty($row->image_path))
                                <a href="{{ asset('storage/' . ltrim($row->image_path, '/')) }}" target="_blank" rel="noopener" class="btn-secondary text-xs px-3 py-2">View photo</a>
                            @endif
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2 text-xs sm:text-sm">
                            <div>
                                <dt class="text-slate-500">Total hours</dt>
                                <dd class="text-slate-200">
                                    @if($row->type === 'check_out' && $row->total_hours !== null)
                                        {{ number_format((float) $row->total_hours, 2) }} h
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Face</dt>
                                <dd class="text-slate-200">{{ $row->face_confidence ? $row->face_confidence . '%' : '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Liveness</dt>
                                <dd class="text-slate-200">{{ $row->liveness_score ? number_format($row->liveness_score, 2) : '—' }}</dd>
                            </div>
                            <div class="col-span-2 min-w-0">
                                <dt class="text-slate-500">Device</dt>
                                <dd class="text-slate-300 break-all">{{ $row->device_id ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400">No attendance records yet.</div>
                @endforelse
            </div>

            <div class="hidden md:block overflow-x-auto -mx-1 px-1">
                <table class="table-glass min-w-[640px] w-full">
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Type</th>
                            <th>Captured At</th>
                            <th>Total hours</th>
                            <th>Face Match</th>
                            <th>Liveness</th>
                            <th>Device</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $row)
                            <tr id="attendance-row-{{ $row->id }}">
                                <td class="font-medium">{{ $row->driver->name ?? 'Unknown' }}</td>
                                <td>
                                    <span class="px-2 py-1 rounded text-xs {{ $row->type === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                                        {{ str_replace('_', ' ', $row->type) }}
                                    </span>
                                </td>
                                <td>{{ $row->captured_at?->format('M d, H:i') }}</td>
                                <td class="tabular-nums text-slate-200">
                                    @if($row->type === 'check_out' && $row->total_hours !== null)
                                        {{ number_format((float) $row->total_hours, 2) }} h
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $row->face_confidence ? $row->face_confidence . '%' : '—' }}</td>
                                <td>{{ $row->liveness_score ? number_format($row->liveness_score, 2) : '—' }}</td>
                                <td class="text-slate-300 max-w-[12rem] break-all">{{ $row->device_id ?? '—' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('attendance.show', $row) }}" class="btn-secondary text-xs px-3 py-2">View</a>
                                        @if(!empty($row->image_path))
                                            <a href="{{ asset('storage/' . ltrim($row->image_path, '/')) }}" target="_blank" rel="noopener" class="btn-secondary text-xs px-3 py-2">Photo</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400">No attendance records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendances->hasPages())
                <div class="mt-4 overflow-x-auto pb-1">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if($role === 'driver')
<script>
    (function() {
        const history = @json(data_get($driverHistoryData, 'history', []));
        const calendarData = @json(data_get($driverHistoryData, 'calendar', []));
        const tbody = document.getElementById('driverHistoryTableBody');

        function renderRows(filter) {
            if (!tbody) return;
            const now = new Date();
            const startOfWeek = new Date(now);
            startOfWeek.setDate(now.getDate() - now.getDay());
            startOfWeek.setHours(0, 0, 0, 0);
            const rows = history.filter((row) => {
                const d = new Date(row.captured_at);
                if (filter === 'daily') return d.toDateString() === now.toDateString();
                if (filter === 'weekly') return d >= startOfWeek;
                if (filter === 'monthly') return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
                if (filter === 'yearly') return d.getFullYear() === now.getFullYear();
                return true;
            });
            tbody.innerHTML = rows.length ? rows.map((row) => {
                const typeLabel = row.type.replace('_', ' ');
                const statusLine = (row.type === 'check_in' && row.status)
                    ? `<span class="block text-[10px] text-slate-400 mt-0.5">${row.status}</span>`
                    : '';
                const hoursCell = (row.type === 'check_out' && row.total_hours != null)
                    ? Number(row.total_hours).toFixed(2) + ' h'
                    : '—';
                return `
                <tr>
                    <td class="px-2 py-2 sm:px-4 sm:py-3 align-top">
                        <div class="flex flex-col items-start gap-0.5">
                            <span class="px-2 py-0.5 rounded text-[10px] sm:text-xs whitespace-nowrap ${row.type === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200'}">${typeLabel}</span>
                            ${statusLine}
                        </div>
                    </td>
                    <td class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap align-top">${row.captured_label || '—'}</td>
                    <td class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap tabular-nums align-top">${hoursCell}</td>
                    <td class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap hidden sm:table-cell align-top">${row.face_confidence ? row.face_confidence + '%' : '—'}</td>
                    <td class="px-2 py-2 sm:px-4 sm:py-3 whitespace-nowrap hidden sm:table-cell align-top">${row.liveness_score ? Number(row.liveness_score).toFixed(2) : '—'}</td>
                    <td class="px-2 py-2 sm:px-4 sm:py-3 max-w-[100px] sm:max-w-[12rem] truncate sm:whitespace-normal sm:break-normal text-slate-300 align-top">${row.device_id || '—'}</td>
                </tr>
            `;
            }).join('') : '<tr><td colspan="6" class="text-center py-8 text-slate-400">No history for this period.</td></tr>';
        }

        document.querySelectorAll('.history-filter-btn').forEach((btn) => {
            btn.addEventListener('click', () => renderRows(btn.dataset.filter || 'all'));
        });
        renderRows('all');

        const calendar = document.getElementById('driverCalendar');
        if (calendar && calendarData) {
            const y = calendarData.year ?? new Date().getFullYear();
            const m = String(calendarData.month ?? (new Date().getMonth() + 1)).padStart(2, '0');
            const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
            const parts = [];
            dayNames.forEach((d) => parts.push(`<span class="text-slate-400 font-medium text-[10px] sm:text-xs py-1">${d}</span>`));
            for (let i = 0; i < (calendarData.firstDayOfWeek || 0); i++) parts.push('<span class="min-w-0 aspect-square" aria-hidden="true"></span>');
            for (let day = 1; day <= (calendarData.daysInMonth || 0); day++) {
                const dateKey = `${y}-${m}-${String(day).padStart(2, '0')}`;
                const status = (calendarData.days || {})[dateKey] ?? null;
                let wrap = 'border border-white/15 bg-transparent text-slate-400';
                let mark = '';
                let label = '';
                if (status === 'present') {
                    wrap = 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-200/90';
                    mark = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400/45 mt-0.5 shrink-0" title="Present"></span>';
                    label = '<span class="sr-only">Present</span>';
                } else if (status === 'late') {
                    wrap = 'border border-amber-500/35 bg-amber-500/10 text-amber-200/90';
                    mark = '<span class="w-1.5 h-1.5 rounded-full bg-amber-400/45 mt-0.5 shrink-0" title="Late"></span>';
                    label = '<span class="sr-only">Late</span>';
                } else if (status === 'absent') {
                    wrap = 'border border-rose-500/30 bg-rose-500/10 text-rose-200/90';
                    mark = '<span class="w-1.5 h-1.5 rounded-full bg-rose-400/45 mt-0.5 shrink-0" title="Absent"></span>';
                    label = '<span class="sr-only">Absent</span>';
                }
                parts.push(`<span class="min-w-0 aspect-square max-w-full flex flex-col items-center justify-center rounded-md sm:rounded-lg text-[10px] sm:text-[11px] md:text-[10px] font-medium leading-tight py-0.5 sm:py-0.5 ${wrap}" data-date="${dateKey}">${label}<span class="tabular-nums">${day}</span>${mark}</span>`);
            }
            calendar.innerHTML = parts.join('');
        }
    })();
</script>
@endif
@if($role === 'admin' && !empty($adminChartData))
<script>
    (function () {
        if (typeof Chart === 'undefined') return;
        const data = @json($adminChartData);

        const statusCtx = document.getElementById('attendanceStatusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: ['Today', 'This Week', 'This Month'],
                    datasets: [
                        {
                            label: 'Present',
                            data: [data.today.present, data.week.present, data.month.present],
                            backgroundColor: 'rgba(16,185,129,0.7)',
                        },
                        {
                            label: 'Late',
                            data: [data.today.late, data.week.late, data.month.late],
                            backgroundColor: 'rgba(245,158,11,0.8)',
                        },
                        {
                            label: 'Absent',
                            data: [data.today.absent, data.week.absent, data.month.absent],
                            backgroundColor: 'rgba(248,113,113,0.85)',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#cbd5e1' },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(255,255,255,0.05)' },
                        },
                        x: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(255,255,255,0.05)' },
                        },
                    },
                },
            });
        }

        const todayPieCtx = document.getElementById('attendanceTodayPieChart');
        if (todayPieCtx && data.today) {
            const present = data.today.present || 0;
            const late = data.today.late || 0;
            const absent = data.today.absent || 0;
            if (present + late + absent > 0) {
                new Chart(todayPieCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Present', 'Late', 'Absent'],
                        datasets: [{
                            data: [present, late, absent],
                            backgroundColor: ['rgba(16,185,129,0.85)', 'rgba(245,158,11,0.85)', 'rgba(239,68,68,0.85)'],
                            borderColor: ['rgb(16,185,129)', 'rgb(245,158,11)', 'rgb(239,68,68)'],
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#cbd5e1' } },
                        },
                    },
                });
            } else {
                todayPieCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-64 text-slate-400">No data for today</div>';
            }
        }
    })();
</script>
@endif
@endpush

