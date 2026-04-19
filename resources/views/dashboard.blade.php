@extends('layouts.app')

@php
    $role = mb_strtolower(trim((string) (auth()->user()?->role ?? '')));
@endphp

@section('content')
<div class="space-y-6">
    @php
        $role = $role ?? mb_strtolower(trim((string) (auth()->user()?->role ?? '')));
    @endphp
    <div class="glass p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <div class="text-sm text-slate-300">Welcome back</div>
            <div class="{{ $role === 'driver' ? 'text-2xl md:text-3xl' : 'text-2xl' }} font-bold text-white">
                {{ auth()->user()->name ?? 'User' }} @if(auth()->user()?->role) <span class="text-sm font-medium text-slate-300">({{ ucfirst(auth()->user()->role) }})</span> @endif
            </div>
        </div>
        @if($role === 'admin')
            <div class="text-sm text-slate-200">System status: <span class="text-emerald-300 font-semibold">Operational</span></div>
        @else
            <div class="text-sm text-slate-200">Ready for your next check-in.</div>
        @endif
    </div>

    @if($role === 'driver')
    <div class="space-y-4 md:space-y-5">
        <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <div class="glass p-4 sm:p-5 min-w-0">
                <h2 class="text-sm sm:text-base font-semibold text-white leading-tight">Check-in Status</h2>
                <p class="text-sm text-slate-300 mt-1.5 break-words">{{ data_get($driverDashboard, 'status.label', 'Not checked in') }}</p>
                <div class="mt-2 text-xs text-slate-400 leading-snug">
                    @if(data_get($driverDashboard, 'status.lastCheckInAt'))
                        Last check-in: {{ data_get($driverDashboard, 'status.lastCheckInAt')?->format('M d, h:i A') }}
                    @else
                        Last check-in: none today
                    @endif
                </div>
            </div>

            <div class="glass p-4 sm:p-5 min-w-0 flex flex-col justify-center">
                <div class="inline-flex items-center gap-1.5 sm:gap-2 text-slate-200 max-w-full min-w-0" role="timer" aria-live="polite" aria-label="Current time">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="driverHomeClock" class="font-mono text-lg sm:text-xl md:text-2xl tracking-tight text-emerald-300 tabular-nums">--:--:--</span>
                </div>
            </div>
        </div>

        <div class="glass p-5 md:p-6">
            <h2 class="text-lg font-semibold text-white">Camera</h2>
            <p class="text-sm text-slate-300 mt-1.5">Use camera to check in/out.</p>
            <p id="driverLiveLocationStatus" class="text-xs text-slate-400 mt-2">
                
            </p>
            <div class="mt-4">
                <a href="{{ route('camera.index') }}" class="btn-primary text-sm px-4 py-2.5">Camera Check-in</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:gap-5">
        <div class="glass p-5 md:p-6">
            <h3 class="text-lg font-semibold text-white">Last Activity</h3>
            @if(data_get($driverDashboard, 'lastActivity'))
                <div class="mt-3 text-sm text-slate-200">
                    <span class="px-2.5 py-1 rounded-md text-xs {{ data_get($driverDashboard, 'lastActivity.type') === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                        {{ str_replace('_', ' ', data_get($driverDashboard, 'lastActivity.type')) }}
                    </span>
                    <div class="mt-2 text-slate-300 text-sm">{{ data_get($driverDashboard, 'lastActivity.captured_at')?->format('M d, Y h:i A') }}</div>
                </div>
            @else
                <div class="mt-3 text-sm text-slate-400">No activity yet.</div>
            @endif
        </div>

    </div>
    @endif

    @if($role === 'admin')
    @php
        $todayCheckInsVal = (int) ($todayCheckIns ?? 0);
        $todayCheckOutsVal = (int) ($todayCheckOuts ?? 0);
        $maxToday = max($todayCheckInsVal, $todayCheckOutsVal, 1);
        $checkInPct = round(($todayCheckInsVal / $maxToday) * 100);
        $checkOutPct = round(($todayCheckOutsVal / $maxToday) * 100);
    @endphp
    <div class="grid grid-cols-3 gap-2 sm:gap-4">
        <div class="glass p-3 sm:p-6 min-w-0">
            <p class="text-[10px] sm:text-xs uppercase tracking-wide text-slate-400 leading-tight">Total Drivers</p>
            <p class="mt-1 sm:mt-2 text-xl sm:text-2xl md:text-3xl font-bold text-white tabular-nums">{{ $driverCount }}</p>
        </div>
        <div class="glass p-3 sm:p-6 min-w-0">
            <p class="text-[10px] sm:text-xs uppercase tracking-wide text-slate-400 leading-tight"><span class="hidden sm:inline">Today's </span>Check-ins</p>
            <p class="mt-1 sm:mt-2 text-xl sm:text-2xl md:text-3xl font-bold text-emerald-300 tabular-nums">{{ $todayCheckInsVal ?? (int) ($todayCheckIns ?? 0) }}</p>
        </div>
        <div class="glass p-3 sm:p-6 min-w-0">
            <p class="text-[10px] sm:text-xs uppercase tracking-wide text-slate-400 leading-tight"><span class="hidden sm:inline">Today's </span>Check-outs</p>
            <p class="mt-1 sm:mt-2 text-xl sm:text-2xl md:text-3xl font-bold text-blue-300 tabular-nums">{{ $todayCheckOutsVal ?? (int) ($todayCheckOuts ?? 0) }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="glass p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Today's Flow</h3>
                    <p class="text-xs text-slate-300">Relative activity by type</p>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-xs text-slate-300 mb-1">
                        <span>Check-ins</span><span>{{ $todayCheckInsVal ?? (int) ($todayCheckIns ?? 0) }}</span>
                    </div>
                    <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-400/70" style="width: {{ $checkInPct ?? 0 }}%;"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-300 mb-1">
                        <span>Check-outs</span><span>{{ $todayCheckOutsVal ?? (int) ($todayCheckOuts ?? 0) }}</span>
                    </div>
                    <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-400/70" style="width: {{ $checkOutPct ?? 0 }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="glass p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Admin Shortcuts</h3>
                    <p class="text-xs text-slate-300">Quick access to key areas</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <a href="{{ route('reports.index') }}" class="nav-link">Reports</a>
                <a href="{{ route('audit-logs.index') }}" class="nav-link">Audit Logs</a>
                <a href="{{ route('users.index', ['sort' => 'role_driver']) }}" class="nav-link">Drivers</a>
                <a href="{{ route('users.index') }}" class="nav-link">Users</a>
                    <a href="{{ route('announcements.index') }}" class="nav-link">Announcements</a>
                <a href="{{ route('attendance.index') }}" class="nav-link">Attendance</a>
                <a href="{{ route('settings.index') }}" class="nav-link">Settings</a>
            </div>
        </div>
    </div>

    @if($role === 'admin' && !empty($chartData))
    <div class="glass p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Status Breakdown (This Week)</h3>
        <p class="text-xs text-slate-300 mb-4">Present, Late & Absent by date for the current week</p>
        <div class="h-64">
            <canvas id="statusBreakdownWeekChart"></canvas>
        </div>
    </div>
    @endif

    @if(!empty($chartData))
    <!-- Charts Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <!-- Weekly Trends Line Chart -->
        <div class="glass p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">7-Day Attendance Trends</h3>
                    <p class="text-xs text-slate-300">Check-ins vs Check-outs over the week</p>
                </div>
            </div>
            <div class="h-64">
                <canvas id="weeklyTrendsChart"></canvas>
            </div>
        </div>

        <!-- Top Drivers Pie Chart -->
        <div class="glass p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Top Drivers (Last 30 Days)</h3>
                    <p class="text-xs text-slate-300">Most active drivers by attendance count</p>
                </div>
            </div>
            <div class="h-64">
                <canvas id="topDriversChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <!-- Hourly Distribution Bar Chart -->
        <div class="glass p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Today's Hourly Distribution</h3>
                    <p class="text-xs text-slate-300">Attendance activity by hour</p>
                </div>
            </div>
            <div class="h-64">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Week Comparison Bar Chart -->
        <div class="glass p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Week Comparison</h3>
                    <p class="text-xs text-slate-300">This week vs last week</p>
                </div>
            </div>
            <div class="h-64">
                <canvas id="weekComparisonChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Chart.js is loaded via CDN, so Chart is available globally

        // Chart.js default configuration for dark theme
        Chart.defaults.color = '#cbd5e1';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
        Chart.defaults.backgroundColor = 'rgba(255, 255, 255, 0.05)';

        const chartData = @json($chartData);

        // Status Breakdown (This Week) - by date
        const statusWeekCtx = document.getElementById('statusBreakdownWeekChart');
        if (statusWeekCtx && chartData.statusByDateLabels && chartData.statusByDateLabels.length) {
            new Chart(statusWeekCtx, {
                type: 'bar',
                data: {
                    labels: chartData.statusByDateLabels,
                    datasets: [
                        { label: 'Present', data: chartData.statusByDatePresent || [], backgroundColor: 'rgba(16, 185, 129, 0.7)', borderColor: 'rgba(16, 185, 129, 0.9)', borderWidth: 1 },
                        { label: 'Late', data: chartData.statusByDateLate || [], backgroundColor: 'rgba(245, 158, 11, 0.7)', borderColor: 'rgba(245, 158, 11, 0.9)', borderWidth: 1 },
                        { label: 'Absent', data: chartData.statusByDateAbsent || [], backgroundColor: 'rgba(239, 68, 68, 0.7)', borderColor: 'rgba(239, 68, 68, 0.9)', borderWidth: 1 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#cbd5e1' } } },
                    scales: {
                        y: { beginAtZero: true, stacked: false, ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        x: { stacked: false, ticks: { color: '#94a3b8' }, grid: { display: false } },
                    },
                },
            });
        }

        // Weekly Trends Line Chart
        const weeklyCtx = document.getElementById('weeklyTrendsChart');
        if (weeklyCtx) {
            new Chart(weeklyCtx, {
                type: 'line',
                data: {
                    labels: chartData.weeklyLabels,
                    datasets: [{
                        label: 'Check-ins',
                        data: chartData.weeklyCheckIns,
                        borderColor: 'rgba(16, 185, 129, 0.8)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Check-outs',
                        data: chartData.weeklyCheckOuts,
                        borderColor: 'rgba(59, 130, 246, 0.8)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#cbd5e1'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        }
                    }
                }
            });
        }

        // Top Drivers Pie Chart
        const driversCtx = document.getElementById('topDriversChart');
        if (driversCtx && chartData.driverLabels.length > 0) {
            new Chart(driversCtx, {
                type: 'doughnut',
                data: {
                    labels: chartData.driverLabels,
                    datasets: [{
                        data: chartData.driverCounts,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(251, 146, 60, 0.8)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(139, 92, 246, 1)',
                            'rgba(236, 72, 153, 1)',
                            'rgba(251, 146, 60, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#cbd5e1',
                                padding: 15
                            }
                        }
                    }
                }
            });
        } else if (driversCtx) {
            driversCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-slate-400">No driver data available</div>';
        }

        // Hourly Distribution Bar Chart
        const hourlyCtx = document.getElementById('hourlyChart');
        if (hourlyCtx) {
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: chartData.hourlyLabels.filter((_, i) => i % 2 === 0), // Show every other hour
                    datasets: [{
                        label: 'Check-ins',
                        data: chartData.hourlyCheckIns.filter((_, i) => i % 2 === 0),
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgba(16, 185, 129, 0.8)',
                        borderWidth: 1
                    }, {
                        label: 'Check-outs',
                        data: chartData.hourlyCheckOuts.filter((_, i) => i % 2 === 0),
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgba(59, 130, 246, 0.8)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#cbd5e1'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        }
                    }
                }
            });
        }

        // Week Comparison Bar Chart
        const weekCompCtx = document.getElementById('weekComparisonChart');
        if (weekCompCtx) {
            new Chart(weekCompCtx, {
                type: 'bar',
                data: {
                    labels: ['This Week', 'Last Week'],
                    datasets: [{
                        label: 'Total Attendance',
                        data: [chartData.thisWeekTotal, chartData.lastWeekTotal],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(59, 130, 246, 0.6)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(59, 130, 246, 0.8)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            }
                        }
                    }
                }
            });
        }
    </script>
    @endpush
    @endif

    <!-- Recent Activity (always visible to admin) -->
    <div class="glass p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-lg font-semibold text-white">Recent Activity</h3>
            <a href="{{ route('attendance.index') }}" class="btn-secondary text-xs px-3 py-2">View all</a>
        </div>
        <ul class="space-y-2">
            @forelse ($recentActivity ?? [] as $row)
                <li class="flex items-center gap-3 py-2.5 border-b border-white/5 last:border-0">
                    <span class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-xs font-medium {{ data_get($row, 'type') === 'check_in' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-blue-500/20 text-blue-300' }}">
                        {{ data_get($row, 'type') === 'check_in' ? 'In' : 'Out' }}
                    </span>
                    <div class="min-w-0 flex-1 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                        <span class="font-medium text-white">{{ data_get($row, 'driver.name', 'Unknown') }}</span>
                        <span class="text-slate-400">{{ data_get($row, 'type') === 'check_in' ? 'checked in' : 'checked out' }}</span>
                        <span class="text-slate-500 text-sm">· {{ data_get($row, 'captured_at')?->format('M j, g:i A') }}</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(data_get($row, 'id'))
                            <a href="{{ route('attendance.show', data_get($row, 'id')) }}" class="btn-secondary text-xs px-3 py-2">View</a>
                        @endif
                    </div>
                </li>
            @empty
                <li class="text-slate-400 py-6 text-center">No recent activity.</li>
            @endforelse
        </ul>
    </div>
    @endif

</div>
@endsection

@push('scripts')
@if($role === 'driver')
<script>
    (function() {
        const el = document.getElementById('driverHomeClock');
        if (!el) return;
        function pad(n) { return n < 10 ? '0' + n : String(n); }
        function tick() {
            const d = new Date();
            const hours24 = d.getHours();
            const meridiem = hours24 >= 12 ? 'PM' : 'AM';
            const hours12 = hours24 % 12 || 12;
            el.textContent = `${pad(hours12)}:${pad(d.getMinutes())}:${pad(d.getSeconds())} ${meridiem}`;
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>
<script>
    (function () {
        if (!navigator.geolocation) {
            const statusEl = document.getElementById('driverLiveLocationStatus');
            if (statusEl) statusEl.textContent = 'Location is not supported on this browser.';
            return;
        }

        const sharingEnabled = @json(!empty($driverLocationSharingEnabled));
        const statusEl = document.getElementById('driverLiveLocationStatus');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const endpoint = @json(route('locations.live-update'));
        const enableEndpoint = @json(route('locations.enable-sharing'));

        let lastSentAt = 0;
        const minIntervalMs = 15000;
        let watchId = null;

        function setStatus(text) {
            if (statusEl) statusEl.textContent = text;
        }

        function sendLocation(position) {
            const now = Date.now();
            if (now - lastSentAt < minIntervalMs) return;
            lastSentAt = now;

            const body = JSON.stringify({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                geo_accuracy: position.coords.accuracy,
                speed: position.coords.speed ?? null,
                heading: position.coords.heading ?? null,
            });

            const postLive = () =>
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body,
                });

            postLive()
                .then(async (response) => {
                    if (response.status === 409) {
                        const en = await fetch(enableEndpoint, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });
                        if (en.ok) return postLive();
                    }
                    return response;
                })
                .then((response) => (response && response.ok ? response.json() : Promise.reject(response)))
                .then(() => setStatus('Live location shared with admin.'))
                .catch(() => setStatus('Could not update live location. Enable “Share live location” in Settings or try again.'));
        }

        function startWatch() {
            if (watchId !== null) return;
            watchId = navigator.geolocation.watchPosition(
                sendLocation,
                () => setStatus('Location permission denied or unavailable.'),
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 10000 }
            );
        }

        if (sharingEnabled) {
            startWatch();
            return;
        }

        setStatus('Live location sharing is off. Tap below to enable it for admin visibility.');
        const wrap = statusEl && statusEl.parentElement;
        if (!wrap) return;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'mt-2 btn-secondary text-xs px-3 py-2';
        btn.textContent = 'Enable live location';
        btn.addEventListener('click', () => {
            btn.disabled = true;
            setStatus('Requesting location permission…');
            navigator.geolocation.getCurrentPosition(
                async () => {
                    try {
                        await fetch(enableEndpoint, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });
                    } catch (_e) {
                        /* non-blocking */
                    }
                    setStatus('Live location enabled.');
                    startWatch();
                },
                () => {
                    setStatus('Location permission denied. Allow location in your browser to share your position.');
                    btn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });
        wrap.appendChild(btn);
    })();
</script>
<script>
    (function () {
        const cfg = @json($driverReminderClient ?? null);
        if (!cfg || !cfg.enabled || !cfg.adminReminders) return;

        function playSound() {
            if (!cfg.sound) return;
            if (typeof window.polarisPlayNotifySound === 'function') {
                window.polarisPlayNotifySound();
                return;
            }
            try {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) return;
                const ctx = new Ctx();
                const o = ctx.createOscillator();
                const g = ctx.createGain();
                o.connect(g);
                g.connect(ctx.destination);
                o.type = 'sine';
                o.frequency.value = 660;
                g.gain.setValueAtTime(0.06, ctx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.18);
                o.start(ctx.currentTime);
                o.stop(ctx.currentTime + 0.18);
            } catch (e) { /* ignore */ }
        }

        function todayKey(suffix) {
            return 'polaris_drv_rm_' + suffix + '_' + new Date().toISOString().slice(0, 10);
        }

        function parseToday(hm) {
            const p = String(hm || '09:00').split(':');
            const d = new Date();
            d.setHours(parseInt(p[0], 10) || 0, parseInt(p[1], 10) || 0, 0, 0);
            return d.getTime();
        }

        function canFireRepeat(storageKey) {
            if (!cfg.repeat) {
                return sessionStorage.getItem(storageKey) !== '1';
            }
            const last = parseInt(sessionStorage.getItem(storageKey) || '0', 10);
            if (!last) return true;
            return Date.now() - last >= cfg.snoozeMin * 60000;
        }

        function markFire(storageKey) {
            if (!cfg.repeat) {
                sessionStorage.setItem(storageKey, '1');
            } else {
                sessionStorage.setItem(storageKey, String(Date.now()));
            }
        }

        function maybeNotify() {
            if (!cfg.showNotifications || !('Notification' in window)) return;
            if (Notification.permission !== 'granted') return;

            const now = Date.now();
            const ciTarget = parseToday(cfg.checkinTime);
            const coTarget = parseToday(cfg.checkoutTime);
            const beforeMs = cfg.beforeMin * 60000;

            if (
                cfg.notifyCheckin &&
                !cfg.hasCheckedInToday &&
                now >= ciTarget - beforeMs &&
                now <= ciTarget + 3 * 3600000
            ) {
                const key = todayKey('ci');
                if (canFireRepeat(key)) {
                    new Notification('Check-in reminder', {
                        body: 'Remember to check in.',
                        silent: !!cfg.sound,
                    });
                    if (cfg.sound) playSound();
                    markFire(key);
                }
            }

            if (
                cfg.notifyCheckout &&
                cfg.hasCheckedInToday &&
                !cfg.hasCheckedOutToday &&
                now >= coTarget - beforeMs &&
                now <= coTarget + 4 * 3600000
            ) {
                const key = todayKey('co');
                if (canFireRepeat(key)) {
                    new Notification('Check-out reminder', {
                        body: 'Remember to check out.',
                        silent: !!cfg.sound,
                    });
                    if (cfg.sound) playSound();
                    markFire(key);
                }
            }
        }

        if (cfg.showNotifications && 'Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().catch(function () {});
        }

        maybeNotify();
        setInterval(maybeNotify, 30000);
    })();
</script>
@endif
@endpush

