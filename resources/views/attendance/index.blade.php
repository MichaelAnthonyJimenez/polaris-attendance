@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="glass p-6">
        <h1 class="text-2xl font-bold text-white mb-6">Record Attendance</h1>
        <form method="POST" action="{{ route('attendance.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if(auth()->user()?->role === 'driver')
                <input type="hidden" name="driver_id" value="{{ $driverSelfId }}">
                <div>
                    <label class="form-label">Type</label>
                    <select name="type" required class="form-select">
                        <option value="check_in">Check In</option>
                        <option value="check_out">Check Out</option>
                    </select>
                </div>
                <div class="space-y-3">
                    <div class="glass p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="text-white font-semibold">Camera capture</div>
                                <div class="text-xs text-slate-300">Take a quick photo as evidence.</div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="camStart" class="btn-primary text-xs">Start</button>
                                <button type="button" id="camStop" class="btn-secondary text-xs">Stop</button>
                                <button type="button" id="camSnap" class="btn-secondary text-xs">Capture</button>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-3">
                            <div class="rounded-xl overflow-hidden border border-white/10 bg-black/40">
                                <video id="camVideo" class="w-full aspect-video" autoplay playsinline muted></video>
                            </div>
                            <div class="rounded-xl overflow-hidden border border-white/10 bg-black/40 flex items-center justify-center">
                                <canvas id="camCanvas" class="w-full"></canvas>
                            </div>
                        </div>
                        <input type="hidden" name="face_image_data" id="face_image_data">
                        <p class="text-xs text-slate-400 mt-2">Capture attaches automatically.</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Driver</label>
                        <select name="driver_id" required class="form-select">
                            <option value="">Select driver</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->badge_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Type</label>
                        <select name="type" required class="form-select">
                            <option value="check_in">Check In</option>
                            <option value="check_out">Check Out</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Face Capture (Optional)</label>
                    <input type="file" name="face_image" accept="image/*" class="form-input">
                    <p class="text-xs text-slate-400 mt-1.5">Used for face matching & liveness detection.</p>
                </div>
            @endif
            <div>
                <button type="submit" class="btn-primary">Save Attendance</button>
            </div>
        </form>
    </div>

    <div class="glass p-6">
        <h2 class="text-xl font-bold text-white mb-4">Recent Attendance</h2>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Captured At</th>
                        <th>Face Match</th>
                        <th>Liveness</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $row)
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
                            <td class="text-slate-300">{{ $row->device_id ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">No attendance records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
            <div class="mt-4">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const video = document.getElementById('camVideo');
        const canvas = document.getElementById('camCanvas');
        const startBtn = document.getElementById('camStart');
        const stopBtn = document.getElementById('camStop');
        const snapBtn = document.getElementById('camSnap');
        const hiddenInput = document.getElementById('face_image_data');
        let stream;

        async function startCam() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = stream;
            } catch (err) {
                alert('Unable to access camera. Please allow camera permissions.');
                console.error(err);
            }
        }

        function stopCam() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
                video.srcObject = null;
            }
        }

        function snap() {
            if (!video.videoWidth) return;
            const ctx = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            hiddenInput.value = canvas.toDataURL('image/png');
        }

        startBtn?.addEventListener('click', startCam);
        stopBtn?.addEventListener('click', stopCam);
        snapBtn?.addEventListener('click', snap);
        window.addEventListener('beforeunload', stopCam);
    })();
</script>
@endpush

