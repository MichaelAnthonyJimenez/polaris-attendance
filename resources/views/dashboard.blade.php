@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="glass p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <div class="text-sm text-slate-300">Welcome back</div>
            <div class="text-2xl font-bold text-white">
                {{ auth()->user()->name ?? 'User' }} @if(auth()->user()?->role) <span class="text-sm font-medium text-slate-300">({{ ucfirst(auth()->user()->role) }})</span> @endif
            </div>
        </div>
        @if(auth()->user()?->role === 'admin')
            <div class="text-sm text-slate-200">System status: <span class="text-emerald-300 font-semibold">Operational</span></div>
        @else
            <div class="text-sm text-slate-200">Ready for your next check-in.</div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass p-6">
            <div class="text-sm text-slate-300 mb-1">Total Drivers</div>
            <div class="text-4xl font-bold text-white">{{ $driverCount }}</div>
        </div>
        <div class="glass p-6">
            <div class="text-sm text-slate-300 mb-1">Today Check-ins</div>
            <div class="text-4xl font-bold text-white">{{ $todayCheckIns }}</div>
        </div>
        <div class="glass p-6">
            <div class="text-sm text-slate-300 mb-1">Today Check-outs</div>
            <div class="text-4xl font-bold text-white">{{ $todayCheckOuts }}</div>
        </div>
    </div>

    <div class="glass p-6">
        <h2 class="text-xl font-bold text-white mb-4">Latest Attendance</h2>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Captured</th>
                        <th>Face Match</th>
                        <th>Liveness</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestAttendance as $row)
                        <tr>
                            <td class="font-medium">{{ $row->driver->name ?? 'Unknown' }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-xs {{ $row->type === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                                    {{ str_replace('_', ' ', $row->type) }}
                                </span>
                            </td>
                            <td>{{ $row->captured_at?->format('M d, H:i') }}</td>
                            <td>
                                @if($row->face_confidence)
                                    <span class="text-emerald-300">{{ $row->face_confidence }}%</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->liveness_score)
                                    <span class="text-emerald-300">{{ number_format($row->liveness_score, 2) }}</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-400">No attendance records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(auth()->user()?->role === 'admin')
    @php
        $maxToday = max($todayCheckIns, $todayCheckOuts, 1);
        $checkInPct = round(($todayCheckIns / $maxToday) * 100);
        $checkOutPct = round(($todayCheckOuts / $maxToday) * 100);
    @endphp
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
                        <span>Check-ins</span><span>{{ $todayCheckIns }}</span>
                    </div>
                    <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-400/70" style="width: {{ $checkInPct }}%;"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-300 mb-1">
                        <span>Check-outs</span><span>{{ $todayCheckOuts }}</span>
                    </div>
                    <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-400/70" style="width: {{ $checkOutPct }}%;"></div>
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
                <a href="{{ route('drivers.index') }}" class="nav-link">Drivers</a>
                <a href="{{ route('users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('attendance.index') }}" class="nav-link">Attendance</a>
                <a href="{{ route('settings.index') }}" class="nav-link">Settings</a>
            </div>
        </div>
    </div>

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
    @endif

    @if(auth()->user()?->role === 'driver')
    <div class="glass p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-white">Camera Check-in</h2>
                <p class="muted text-sm">Use your camera to capture a quick selfie before attendance.</p>
            </div>
            <div class="flex gap-2">
                <button id="startCam" type="button" class="btn-primary text-sm">Start Camera</button>
                <button id="stopCam" type="button" class="btn-secondary text-sm">Stop</button>
                <button id="captureBtn" type="button" class="btn-secondary text-sm">Capture</button>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="rounded-xl overflow-hidden border border-white/10 bg-black/40">
                <video id="driverVideo" class="w-full aspect-video" autoplay playsinline muted></video>
            </div>
            <div class="rounded-xl overflow-hidden border border-white/10 bg-black/40 flex items-center justify-center">
                <canvas id="driverCanvas" class="w-full"></canvas>
            </div>
        </div>
        <p class="text-xs text-slate-300 mt-3">Note: Capture is local only. Integrate with attendance upload to store.</p>
    </div>

    <script>
        (() => {
            const video = document.getElementById('driverVideo');
            const canvas = document.getElementById('driverCanvas');
            const startBtn = document.getElementById('startCam');
            const stopBtn = document.getElementById('stopCam');
            const captureBtn = document.getElementById('captureBtn');
            let stream;

            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    video.srcObject = stream;
                } catch (err) {
                    alert('Unable to access camera. Please check permissions.');
                    console.error(err);
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                    video.srcObject = null;
                }
            }

            function captureFrame() {
                if (!video.videoWidth) return;
                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            }

            startBtn?.addEventListener('click', startCamera);
            stopBtn?.addEventListener('click', stopCamera);
            captureBtn?.addEventListener('click', captureFrame);
            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
    @endif
</div>
@endsection

