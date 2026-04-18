@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

@php
    $statusMessage = session('status');
@endphp

<div
    id="cameraShell"
    class="fixed left-0 right-0 top-0 bottom-0 z-[9999] flex min-h-[100dvh] w-full flex-col bg-black text-white"
    style="min-height: -webkit-fill-available;"
    data-auto-capture="{{ $cameraAutoCapture ? '1' : '0' }}"
    data-auto-submit="{{ $cameraAutoSubmit ? '1' : '0' }}"
    data-location-sharing-enabled="{{ !empty($driverLocationSharingEnabled) ? '1' : '0' }}"
>
    <form id="cameraAttendanceForm" method="POST" action="{{ route('attendance.store') }}" class="flex flex-1 flex-col min-h-0">
        @csrf
        <input type="hidden" name="face_image_data" id="face_image_data" value="">
        <input type="hidden" name="type" id="attendance_type" value="check_in">
        <input type="hidden" name="captured_at" id="attendance_captured_at" value="">
        <input type="hidden" name="captured_timezone" id="attendance_captured_timezone" value="">
        <input type="hidden" name="captured_tz_offset" id="attendance_captured_tz_offset" value="">
        <input type="hidden" name="latitude" id="att_latitude" value="">
        <input type="hidden" name="longitude" id="att_longitude" value="">
        <input type="hidden" name="geo_accuracy" id="att_geo_accuracy" value="">

        {{-- Top bar: back + status (check in / out moved beside capture in footer) --}}
        <header
            class="flex shrink-0 items-center gap-3 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 bg-gradient-to-b from-black/80 to-transparent"
            style="padding-left: max(0.75rem, env(safe-area-inset-left)); padding-right: max(0.75rem, env(safe-area-inset-right));"
        >
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white hover:bg-white/20 transition"
                aria-label="Back to dashboard"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>

            <div class="flex-1 min-w-0">
                @if ($statusMessage)
                    <p class="text-xs font-medium text-emerald-300 truncate">{{ $statusMessage }}</p>
                @endif
                @if ($errors->any())
                    <p class="text-xs font-medium text-red-300 truncate">{{ $errors->first() }}</p>
                @endif
                <p id="cameraHint" class="text-xs text-slate-400 truncate @if($statusMessage || $errors->any()) mt-0.5 @endif">
                    Enable camera access when prompted, then capture your photo.
                </p>
            </div>
        </header>

        {{-- Viewport: live camera or preview --}}
        <div class="flex-1 relative min-h-0 bg-black">
            <video
                id="driverVideo"
                class="absolute inset-0 h-full w-full object-cover"
                autoplay
                playsinline
                muted
            ></video>
            <img
                id="previewImg"
                src=""
                alt="Captured photo preview"
                class="absolute inset-0 hidden h-full w-full object-cover"
                width="1"
                height="1"
            />

            <canvas id="driverCanvas" class="hidden" width="2" height="2"></canvas>

            <div
                id="cameraPermissionGate"
                class="hidden absolute inset-0 z-10 flex flex-col items-center justify-center gap-4 bg-black/90 px-6 text-center"
            >
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 max-w-sm">
                    <p class="text-base font-semibold text-white mb-2">Camera access</p>
                    <p id="cameraPermissionText" class="text-sm text-slate-300 mb-5">
                        We need camera access for attendance capture. Please allow <strong class="text-white">camera</strong> when prompted.
                    </p>
                    <button
                        type="button"
                        id="cameraRetryBtn"
                        class="btn-primary w-full justify-center text-sm py-2.5"
                    >
                        Enable camera
                    </button>
                </div>
            </div>
            <div
                id="locationPermissionGate"
                class="hidden absolute inset-0 z-10 flex flex-col items-center justify-center gap-4 bg-black/90 px-6 text-center"
            >
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 max-w-sm">
                    <p class="text-base font-semibold text-white mb-2">Location access</p>
                    <p id="locationPermissionText" class="text-sm text-slate-300 mb-5">
                        Allow <strong class="text-white">location</strong> so live tracking is automatically enabled for admin monitoring. You can turn this off anytime in Settings.
                    </p>
                    <button
                        type="button"
                        id="locationEnableBtn"
                        class="btn-primary w-full justify-center text-sm py-2.5"
                    >
                        Enable location
                    </button>
                </div>
            </div>
        </div>

        {{-- Bottom controls --}}
        <footer
            class="shrink-0 flex flex-col items-center gap-4 px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] bg-gradient-to-t from-black via-black/95 to-transparent"
            style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));"
        >
            <div id="liveControls" class="flex w-full max-w-lg flex-col items-center gap-3 px-2">
                <div class="flex w-full items-end justify-center gap-3 sm:gap-6" role="group" aria-label="Attendance type and capture">
                    <button
                        type="button"
                        id="btnCheckIn"
                        class="cam-type-btn max-w-[6.5rem] flex-1 rounded-xl border border-white/15 bg-black/40 px-3 py-2.5 text-center text-xs font-semibold transition sm:max-w-[7.5rem] sm:px-4 bg-blue-600 text-white shadow"
                        data-type="check_in"
                    >
                        Check in
                    </button>
                    <button
                        type="button"
                        id="captureBtn"
                        class="h-16 w-16 shrink-0 rounded-full border-4 border-white bg-white/20 shadow-lg ring-4 ring-white/30 disabled:opacity-40 disabled:pointer-events-none"
                        aria-label="Capture photo"
                    ></button>
                    <button
                        type="button"
                        id="btnCheckOut"
                        class="cam-type-btn max-w-[6.5rem] flex-1 rounded-xl border border-white/15 bg-black/40 px-3 py-2.5 text-center text-xs font-semibold text-slate-300 transition hover:text-white sm:max-w-[7.5rem] sm:px-4"
                        data-type="check_out"
                    >
                        Check out
                    </button>
                </div>
                <span class="text-center text-xs text-slate-500">Choose check in or check out, then tap the center button to capture</span>
            </div>

            <div id="previewControls" class="hidden flex w-full max-w-md gap-3">
                <button type="button" id="retakeBtn" class="btn-secondary flex-1 text-sm py-3">
                    Retake
                </button>
                <button type="submit" id="submitBtn" class="btn-primary flex-1 text-sm py-3">
                    Submit
                </button>
            </div>
        </footer>
    </form>
</div>

<script>
    (() => {
        const shell = document.getElementById('cameraShell');
        const form = document.getElementById('cameraAttendanceForm');
        const video = document.getElementById('driverVideo');
        const canvas = document.getElementById('driverCanvas');
        const previewImg = document.getElementById('previewImg');
        const captureBtn = document.getElementById('captureBtn');
        const submitBtn = document.getElementById('submitBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const faceInput = document.getElementById('face_image_data');
        const typeInput = document.getElementById('attendance_type');
        const capturedAtInput = document.getElementById('attendance_captured_at');
        const capturedTimezoneInput = document.getElementById('attendance_captured_timezone');
        const capturedTzOffsetInput = document.getElementById('attendance_captured_tz_offset');
        const hint = document.getElementById('cameraHint');
        const permissionGate = document.getElementById('cameraPermissionGate');
        const permissionText = document.getElementById('cameraPermissionText');
        const retryBtn = document.getElementById('cameraRetryBtn');
        const locationPermissionGate = document.getElementById('locationPermissionGate');
        const locationPermissionText = document.getElementById('locationPermissionText');
        const locationEnableBtn = document.getElementById('locationEnableBtn');
        const liveControls = document.getElementById('liveControls');
        const previewControls = document.getElementById('previewControls');
        const typeButtons = document.querySelectorAll('.cam-type-btn');

        const autoCapture = shell?.dataset.autoCapture === '1';
        const autoSubmit = shell?.dataset.autoSubmit === '1';
        let locationSharingEnabled = shell?.dataset.locationSharingEnabled === '1';
        const liveLocationEndpoint = @json(route('locations.live-update'));
        const enableLocationSharingEndpoint = @json(route('locations.enable-sharing'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let stream = null;
        let mode = 'live';
        let autoCaptureScheduled = false;
        let submitting = false;
        let geoWatcherId = null;

        function setHint(text) {
            if (hint) hint.textContent = text;
        }

        function setMode(next) {
            mode = next;
            const live = next === 'live';
            if (live) {
                video.classList.remove('hidden');
                previewImg.classList.add('hidden');
                liveControls.classList.remove('hidden');
                previewControls.classList.add('hidden');
            } else {
                video.classList.add('hidden');
                previewImg.classList.remove('hidden');
                liveControls.classList.add('hidden');
                previewControls.classList.remove('hidden');
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach((t) => t.stop());
                stream = null;
            }
            if (video) video.srcObject = null;
            if (geoWatcherId !== null && navigator.geolocation) {
                navigator.geolocation.clearWatch(geoWatcherId);
                geoWatcherId = null;
            }
        }

        function showPermissionGate(message) {
            if (permissionText && message) permissionText.textContent = message;
            permissionGate?.classList.remove('hidden');
        }

        function hidePermissionGate() {
            permissionGate?.classList.add('hidden');
        }

        function showLocationPermissionGate(message) {
            if (locationPermissionText && message) locationPermissionText.innerHTML = message;
            locationPermissionGate?.classList.remove('hidden');
        }

        function hideLocationPermissionGate() {
            locationPermissionGate?.classList.add('hidden');
        }

        function syncTypeButtons() {
            const v = typeInput.value;
            typeButtons.forEach((btn) => {
                const on = btn.dataset.type === v;
                btn.classList.toggle('bg-blue-600', on);
                btn.classList.toggle('text-white', on);
                btn.classList.toggle('shadow', on);
                btn.classList.toggle('text-slate-300', !on);
                btn.classList.toggle('hover:text-white', !on);
            });
        }

        function beginLiveLocationWatch() {
            if (!locationSharingEnabled || !navigator.geolocation) return;
            if (geoWatcherId !== null) return;

            geoWatcherId = navigator.geolocation.watchPosition(
                (position) => {
                    document.getElementById('att_latitude').value = String(position.coords.latitude);
                    document.getElementById('att_longitude').value = String(position.coords.longitude);
                    if (position.coords.accuracy != null) {
                        document.getElementById('att_geo_accuracy').value = String(position.coords.accuracy);
                    }
                    fetch(liveLocationEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            geo_accuracy: position.coords.accuracy,
                            speed: position.coords.speed ?? null,
                            heading: position.coords.heading ?? null,
                        }),
                    }).catch(() => {});
                },
                () => {},
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 10000 }
            );
        }

        function requestLocationPermissionAndEnable() {
            if (!navigator.geolocation) {
                setHint('Location services are not available on this device.');
                return;
            }

            setHint('Requesting location access…');
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    document.getElementById('att_latitude').value = String(position.coords.latitude);
                    document.getElementById('att_longitude').value = String(position.coords.longitude);
                    if (position.coords.accuracy != null) {
                        document.getElementById('att_geo_accuracy').value = String(position.coords.accuracy);
                    }

                    try {
                        await fetch(enableLocationSharingEndpoint, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                        });
                    } catch (_e) {
                        // keep flow non-blocking
                    }

                    locationSharingEnabled = true;
                    hideLocationPermissionGate();
                    beginLiveLocationWatch();
                    setHint('Location enabled and live tracking is on. You can disable it in Settings anytime.');
                },
                () => {
                    showLocationPermissionGate('Location was denied. Enable it to automatically turn on live tracking for admin visibility. You can still capture attendance with camera.');
                    setHint('Location is still off. You can enable it in Settings later.');
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        async function startCamera() {
            hidePermissionGate();
            hideLocationPermissionGate();
            stopCamera();
            autoCaptureScheduled = false;
            setMode('live');
            try {
                setHint('Opening camera…');
                stream = await window.polarisRequestCameraOnly(video, { setHint });
                setHint('Position your face, then capture.');
                captureBtn.disabled = false;

                if (locationSharingEnabled) {
                    beginLiveLocationWatch();
                } else {
                    showLocationPermissionGate(
                        '<strong class="text-white">Location is off</strong> until you tap Enable location below. You can check in or out with the camera either way.'
                    );
                }

                const scheduleAutoCapture = () => {
                    if (autoCaptureScheduled || !autoCapture) return;
                    autoCaptureScheduled = true;
                    window.setTimeout(() => {
                        if (mode === 'live' && video.videoWidth) captureFrame({ auto: true });
                    }, 750);
                };

                video.addEventListener('playing', scheduleAutoCapture, { once: true });
                window.setTimeout(scheduleAutoCapture, 3500);
            } catch (err) {
                console.error(err);
                captureBtn.disabled = true;
                showPermissionGate(
                    'We could not use the camera. Allow camera access in your browser settings, or tap below to try again.'
                );
                setHint('Camera not available.');
            }
        }

        function captureFrame(opts) {
            const isAuto = opts && opts.auto;
            if (!video.videoWidth) {
                if (!isAuto) setHint('Wait for the camera preview to appear.');
                return;
            }
            const ctx = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            faceInput.value = dataUrl;
            const capturedAt = new Date();
            if (capturedAtInput) capturedAtInput.value = capturedAt.toISOString();
            if (capturedTimezoneInput) {
                try {
                    capturedTimezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                } catch (_e) {
                    capturedTimezoneInput.value = '';
                }
            }
            if (capturedTzOffsetInput) capturedTzOffsetInput.value = String(capturedAt.getTimezoneOffset());
            previewImg.src = dataUrl;
            setMode('preview');
            setHint(isAuto ? 'Photo captured automatically. Submit or retake.' : 'Review your photo, then submit.');

            if (autoSubmit && !submitting) {
                submitting = true;
                form.requestSubmit();
            }
        }

        async function retake() {
            faceInput.value = '';
            previewImg.removeAttribute('src');
            submitting = false;
            submitBtn.disabled = false;
            await startCamera();
        }

        typeButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                typeInput.value = btn.dataset.type || 'check_in';
                syncTypeButtons();
            });
        });
        syncTypeButtons();

        captureBtn?.addEventListener('click', () => captureFrame({ auto: false }));
        retakeBtn?.addEventListener('click', () => retake());
        retryBtn?.addEventListener('click', () => startCamera());
        locationEnableBtn?.addEventListener('click', () => requestLocationPermissionAndEnable());

        form?.addEventListener('submit', () => {
            if (capturedAtInput && !capturedAtInput.value) {
                capturedAtInput.value = new Date().toISOString();
            }
            if (capturedTimezoneInput && !capturedTimezoneInput.value) {
                try {
                    capturedTimezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                } catch (_e) {
                    capturedTimezoneInput.value = '';
                }
            }
            if (capturedTzOffsetInput && !capturedTzOffsetInput.value) {
                capturedTzOffsetInput.value = String(new Date().getTimezoneOffset());
            }
            submitting = true;
            submitBtn.disabled = true;
        });

        captureBtn.disabled = true;
        showPermissionGate(
            locationSharingEnabled
                ? 'We need camera access for attendance. Tap below and allow camera.'
                : 'We need camera access for attendance. Tap below and allow camera.'
        );
        window.addEventListener('beforeunload', stopCamera);
    })();
</script>
@php
    $recordedType = session('attendance_recorded');
@endphp
<script>
    window.polarisPlayNotifySound = window.polarisPlayNotifySound || function () {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = new Ctx();
            const o = ctx.createOscillator();
            const g = ctx.createGain();
            o.connect(g);
            g.connect(ctx.destination);
            o.type = 'sine';
            o.frequency.value = 880;
            g.gain.setValueAtTime(0.07, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);
            o.start(ctx.currentTime);
            o.stop(ctx.currentTime + 0.22);
        } catch (e) { /* ignore */ }
    };
    (function () {
        const type = @json($recordedType);
        if (!type) return;
        const cfg = @json($postCaptureNotify ?? []);
        const wantBrowser =
            cfg.showNotifications &&
            (type === 'check_in' ? cfg.browserCheckin : cfg.browserCheckout);
        if (wantBrowser && 'Notification' in window && Notification.permission === 'granted') {
            const title = type === 'check_in' ? 'Checked in' : 'Checked out';
            new Notification(title, { body: 'Your attendance was saved.', silent: !!cfg.sound });
        }
        if (cfg.sound) {
            window.polarisPlayNotifySound();
        }
    })();
</script>
@endsection
