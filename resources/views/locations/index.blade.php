@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-white">Driver Locations</h1>
            <p class="text-slate-300 text-sm">GPS coordinates from driver check-in and check-out records.</p>
        </div>
        <form method="GET" action="{{ route('locations.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <label for="driver_id" class="text-sm text-slate-300">Driver</label>
            <select id="driver_id" name="driver_id" class="form-select min-w-[220px]">
                <option value="0">All drivers</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected($selectedDriverId === (int) $driver->id)>
                        {{ $driver->name }} @if($driver->badge_number) ({{ $driver->badge_number }}) @endif
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
    </div>

    <div class="glass p-4">
        <div id="map" class="w-full h-64 sm:h-96 rounded-xl overflow-hidden"></div>
    </div>

    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-300 px-1">
        <div class="flex items-center gap-2">
            <span class="inline-block w-3.5 h-3.5 rounded-full bg-emerald-400 border border-white/30"></span>
            <span>Check in</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-3.5 h-3.5 rounded-full bg-amber-400 border border-white/30"></span>
            <span>Check out</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-3.5 h-3.5 rounded-full bg-rose-500 border border-white/30"></span>
            <span>Inactive / location off</span>
        </div>
    </div>

    <div class="glass p-4">
        <h2 class="text-lg font-semibold text-white mb-3">Location Logs</h2>
        <!-- Mobile cards -->
        <div class="space-y-3 md:hidden">
            @forelse($locationLogs as $log)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm text-slate-200 font-medium truncate">
                                {{ $log['driver_name'] }}
                            </div>
                            <div class="text-xs text-slate-400">
                                Badge: {{ $log['driver_badge'] }}
                            </div>
                        </div>
                        <span class="shrink-0 px-2 py-1 rounded text-xs {{ $log['type'] === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-amber-500/20 text-amber-200' }}">
                            {{ $log['type_label'] }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-slate-300">
                        <div>
                            <span class="text-slate-500">Captured:</span>
                            <span class="text-slate-200">{{ $log['captured_label'] }}</span>
                        </div>
                        <div class="text-slate-300">
                            <span class="text-slate-500">Latitude:</span>
                            <span class="text-slate-200">{{ number_format($log['lat'], 7) }}</span>
                        </div>
                        <div class="text-slate-300">
                            <span class="text-slate-500">Longitude:</span>
                            <span class="text-slate-200">{{ number_format($log['lng'], 7) }}</span>
                        </div>
                        @if(!is_null($log['geo_accuracy']))
                            <div>
                                <span class="text-slate-500">Accuracy:</span>
                                <span class="text-slate-200">{{ number_format($log['geo_accuracy'], 1) }} m</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400">No GPS location logs found.</div>
            @endforelse
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Badge</th>
                        <th>Event</th>
                        <th>Captured At</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Accuracy (m)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locationLogs as $log)
                        <tr>
                            <td class="font-medium">{{ $log['driver_name'] }}</td>
                            <td>{{ $log['driver_badge'] }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-xs {{ $log['type'] === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-amber-500/20 text-amber-200' }}">
                                    {{ $log['type_label'] }}
                                </span>
                            </td>
                            <td>{{ $log['captured_label'] }}</td>
                            <td>{{ number_format($log['lat'], 7) }}</td>
                            <td>{{ number_format($log['lng'], 7) }}</td>
                            <td>{{ is_null($log['geo_accuracy']) ? '—' : number_format($log['geo_accuracy'], 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-400">No GPS location logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass p-4">
        <h2 class="text-lg font-semibold text-white mb-3">Live Driver Tracking</h2>
        <div class="space-y-3" id="live-feed-list">
            @forelse($liveFeed as $driver)
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <div class="text-sm text-white font-medium">{{ $driver['driver_name'] }} ({{ $driver['driver_badge'] }})</div>
                            <div class="text-xs text-slate-400">{{ $driver['route_label'] }}</div>
                        </div>
                        <div class="text-xs px-2 py-1 rounded {{ $driver['sharing_enabled'] ? 'bg-emerald-500/20 text-emerald-200' : 'bg-rose-500/20 text-rose-200' }}">
                            {{ $driver['status'] }}
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-400">
                        Last update: {{ $driver['location_updated_label'] }}
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-400">No active drivers available.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const map = L.map('map').setView([14.56, 121.03], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const logs = @json($locationLogs);
        const initialLiveFeed = @json($liveFeed);
        const liveEndpoint = @json(route('locations.live-feed'));
        const selectedDriverId = @json($selectedDriverId);
        const markerGroup = L.featureGroup().addTo(map);
        const liveMarkers = new Map();
        const liveRoutes = new Map();

        const checkInIcon = L.divIcon({
            className: '',
            html: `<div style="
                width: 16px;
                height: 16px;
                border-radius: 9999px;
                background: rgba(16, 185, 129, 0.9);
                border: 2px solid rgba(255, 255, 255, 0.85);
                box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
            "></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
            popupAnchor: [0, -8],
        });

        const checkOutIcon = L.divIcon({
            className: '',
            html: `<div style="
                width: 16px;
                height: 16px;
                border-radius: 9999px;
                background: rgba(245, 158, 11, 0.9);
                border: 2px solid rgba(255, 255, 255, 0.85);
                box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);
            "></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
            popupAnchor: [0, -8],
        });

        const logMarkers = [];
        logs.forEach((log) => {
            const icon = log.type === 'check_in' ? checkInIcon : checkOutIcon;
            const marker = L.marker([log.lat, log.lng], { icon }).addTo(map);
            marker.bindPopup(
                `<strong>${log.driver_name}</strong>` +
                `<br>Badge: ${log.driver_badge}` +
                `<br>Event: ${log.type_label}` +
                `<br>Captured: ${log.captured_label}` +
                `<br>Lat: ${Number(log.lat).toFixed(7)}` +
                `<br>Lng: ${Number(log.lng).toFixed(7)}` +
                (log.geo_accuracy !== null ? `<br>Accuracy: ${Number(log.geo_accuracy).toFixed(1)}m` : '')
            );
            logMarkers.push(marker);
        });

        if (logMarkers.length) {
            const group = L.featureGroup(logMarkers);
            map.fitBounds(group.getBounds().pad(0.2));
        }

        function renderLiveList(feed) {
            const list = document.getElementById('live-feed-list');
            if (!list) return;
            list.innerHTML = '';

            if (!feed.length) {
                list.innerHTML = '<div class="text-sm text-slate-400">No active drivers available.</div>';
                return;
            }

            feed.forEach((driver) => {
                const statusClass = driver.sharing_enabled
                    ? 'bg-emerald-500/20 text-emerald-200'
                    : 'bg-rose-500/20 text-rose-200';

                const card = document.createElement('div');
                card.className = 'rounded-xl border border-white/10 bg-white/5 p-3';
                card.innerHTML = `
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <div class="text-sm text-white font-medium">${driver.driver_name} (${driver.driver_badge})</div>
                            <div class="text-xs text-slate-400">${driver.route_label}</div>
                        </div>
                        <div class="text-xs px-2 py-1 rounded ${statusClass}">
                            ${driver.status}
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-400">Last update: ${driver.location_updated_label}</div>
                `;
                list.appendChild(card);
            });
        }

        function renderLiveMap(feed) {
            const activeIds = new Set();

            feed.forEach((driver) => {
                activeIds.add(driver.driver_id);

                if (!driver.sharing_enabled || driver.lat === null || driver.lng === null) {
                    if (liveMarkers.has(driver.driver_id)) {
                        markerGroup.removeLayer(liveMarkers.get(driver.driver_id));
                        liveMarkers.delete(driver.driver_id);
                    }
                    if (liveRoutes.has(driver.driver_id)) {
                        map.removeLayer(liveRoutes.get(driver.driver_id));
                        liveRoutes.delete(driver.driver_id);
                    }
                    return;
                }

                const latLng = [driver.lat, driver.lng];
                let marker = liveMarkers.get(driver.driver_id);
                if (!marker) {
                    marker = L.marker(latLng).addTo(markerGroup);
                    liveMarkers.set(driver.driver_id, marker);
                } else {
                    marker.setLatLng(latLng);
                }

                marker.bindPopup(
                    `<strong>${driver.driver_name}</strong>` +
                    `<br>Badge: ${driver.driver_badge}` +
                    `<br>Status: ${driver.status}` +
                    `<br>Route: ${driver.route_label}` +
                    `<br>Last update: ${driver.location_updated_label}`
                );

                const points = (driver.route_points || [])
                    .filter((point) => point.lat !== null && point.lng !== null)
                    .map((point) => [point.lat, point.lng]);

                if (points.length >= 2) {
                    let polyline = liveRoutes.get(driver.driver_id);
                    if (!polyline) {
                        polyline = L.polyline(points, { color: '#38bdf8', weight: 3, opacity: 0.75 }).addTo(map);
                        liveRoutes.set(driver.driver_id, polyline);
                    } else {
                        polyline.setLatLngs(points);
                    }
                } else if (liveRoutes.has(driver.driver_id)) {
                    map.removeLayer(liveRoutes.get(driver.driver_id));
                    liveRoutes.delete(driver.driver_id);
                }
            });

            Array.from(liveMarkers.keys()).forEach((driverId) => {
                if (!activeIds.has(driverId)) {
                    markerGroup.removeLayer(liveMarkers.get(driverId));
                    liveMarkers.delete(driverId);
                }
            });
        }

        async function refreshLiveFeed() {
            try {
                const params = new URLSearchParams({ driver_id: String(selectedDriverId) });
                const response = await fetch(`${liveEndpoint}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!response.ok) return;
                const payload = await response.json();
                const liveFeed = Array.isArray(payload.liveFeed) ? payload.liveFeed : [];
                renderLiveList(liveFeed);
                renderLiveMap(liveFeed);
            } catch (error) {
                // ignore transient polling errors
            }
        }

        renderLiveList(initialLiveFeed);
        renderLiveMap(initialLiveFeed);
        window.setInterval(refreshLiveFeed, 15000);
    });
</script>
@endpush

