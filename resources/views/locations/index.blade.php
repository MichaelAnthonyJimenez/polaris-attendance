@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Driver Locations</h1>
            <p class="text-slate-300 text-sm">Visualize driver positions (demo coordinates).</p>
        </div>
    </div>

    <div class="glass p-4">
        <div id="map" class="w-full h-96 rounded-xl overflow-hidden"></div>
    </div>

    <div class="glass p-4">
        <h2 class="text-lg font-semibold text-white mb-3">Drivers</h2>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Badge</th>
                        <th>Status</th>
                        <th>Lat</th>
                        <th>Lng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($driverLocations as $driver)
                        <tr>
                            <td class="font-medium">{{ $driver['name'] }}</td>
                            <td>{{ $driver['badge'] }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-xs {{ $driver['active'] ? 'bg-emerald-500/20 text-emerald-200' : 'bg-slate-500/20 text-slate-200' }}">
                                    {{ $driver['active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ number_format($driver['lat'], 5) }}</td>
                            <td>{{ number_format($driver['lng'], 5) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400">No drivers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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

        const drivers = @json($driverLocations);
        drivers.forEach(d => {
            const marker = L.marker([d.lat, d.lng]).addTo(map);
            marker.bindPopup(`<strong>${d.name}</strong><br>Badge: ${d.badge}<br>Status: ${d.active ? 'Active' : 'Inactive'}`);
        });

        if (drivers.length) {
            const group = L.featureGroup(drivers.map(d => L.marker([d.lat, d.lng])));
            map.fitBounds(group.getBounds().pad(0.2));
        }
    });
</script>
@endpush

