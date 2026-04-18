@extends('layouts.app')

@section('content')
@php
    $meta = is_array($attendance->meta ?? null) ? $attendance->meta : [];
    $latitude = data_get($meta, 'latitude');
    $longitude = data_get($meta, 'longitude');
    $accuracy = data_get($meta, 'geo_accuracy');
    $hasLocation = is_numeric($latitude) && is_numeric($longitude);
@endphp
<div class="space-y-6">
    <div class="glass p-5 sm:p-6 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Attendance Details</h1>
            <p class="text-sm text-slate-300 mt-1">Check-in/check-out information for this record.</p>
        </div>
        <a href="{{ route('attendance.index') }}" class="btn-secondary text-xs sm:text-sm px-3 py-2">Back to records</a>
    </div>

    <div class="glass p-5 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Driver</p>
                <p class="mt-1 text-white font-medium">{{ $attendance->driver->name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Type</p>
                <p class="mt-1">
                    <span class="px-2 py-1 rounded text-xs {{ $attendance->type === 'check_in' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                        {{ str_replace('_', ' ', $attendance->type) }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Captured At</p>
                <p class="mt-1 text-white font-medium">{{ $attendance->captured_at?->format('M d, Y h:i A') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Status</p>
                <p class="mt-1 text-white font-medium">{{ $attendance->status ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Face Match</p>
                <p class="mt-1 text-white font-medium">{{ $attendance->face_confidence ? $attendance->face_confidence . '%' : '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Liveness</p>
                <p class="mt-1 text-white font-medium">{{ $attendance->liveness_score ? number_format($attendance->liveness_score, 2) : '—' }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs uppercase tracking-wide text-slate-400">Device</p>
                <p class="mt-1 text-white font-medium break-all">{{ $attendance->device_id ?? '—' }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs uppercase tracking-wide text-slate-400">Location</p>
                @if($hasLocation)
                    <p class="mt-1 text-white font-medium break-all">
                        {{ number_format((float) $latitude, 6) }}, {{ number_format((float) $longitude, 6) }}
                    </p>
                    <p class="mt-1 text-slate-300 text-sm">
                        Accuracy: {{ is_numeric($accuracy) ? number_format((float) $accuracy, 1) . ' m' : '—' }}
                    </p>
                    <a
                        href="https://www.google.com/maps?q={{ (float) $latitude }},{{ (float) $longitude }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex mt-2 btn-secondary text-xs px-3 py-2"
                    >
                        Open in Maps
                    </a>
                @else
                    <p class="mt-1 text-white font-medium">—</p>
                @endif
            </div>
        </div>
    </div>

    @if(!empty($attendance->image_path))
        <div class="glass p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Captured Photo</h2>
            <img
                src="{{ asset('storage/' . ltrim($attendance->image_path, '/')) }}"
                alt="Attendance capture"
                class="rounded-xl border border-white/10 w-full max-w-xl object-cover"
            >
        </div>
    @endif
</div>
@endsection
