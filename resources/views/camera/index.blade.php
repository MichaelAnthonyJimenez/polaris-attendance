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
                    <div class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <p class="text-xs font-medium text-red-300 truncate">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <p id="cameraHint" class="text-xs text-slate-400 truncate @if($statusMessage || $errors->any()) mt-0.5 @endif">
                    Enable camera access when prompted, then capture your photo.
                </p>
            </div>
        </header>

        <div
            id="coverageGuide"
            class="shrink-0 mx-3 mb-2 rounded-xl border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-[11px] text-amber-100"
            style="margin-left: max(0.75rem, env(safe-area-inset-left)); margin-right: max(0.75rem, env(safe-area-inset-right));"
        >
            <p class="font-semibold text-amber-200">Safety and coverage guide</p>
            <p class="mt-1">
                Avoid check in/check out while driving or while in unoperational or limited-data areas (highways, streets, and roads with unstable signal). Stop in a safe location and wait for accurate GPS before submitting.
            </p>
            <button
                type="button"
                id="coverageGuideOkBtn"
                class="mt-2 w-full rounded-lg bg-amber-600/30 border border-amber-400/50 px-2 py-1.5 text-[10px] font-medium text-amber-100 hover:bg-amber-600/40 transition"
            >
                OK, I understand
            </button>
        </div>

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

            {{-- Live face detection overlay --}}
            <div id="faceDetectionOverlay" class="absolute top-4 left-4 right-4 pointer-events-none">
                <div id="noFaceDetected" class="inline-flex items-center gap-2 bg-red-600/80 backdrop-blur-sm rounded-full px-4 py-2 border border-white/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-white text-sm font-medium">No face detected</span>
                </div>
                <div id="faceDetected" class="hidden inline-flex items-center gap-2 bg-green-600/80 backdrop-blur-sm rounded-full px-4 py-2 border border-white/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-white text-sm font-medium">User Detected: {{ Auth::user()->name }}</span>
                </div>
                <div id="unknownFace" class="hidden inline-flex items-center gap-2 bg-amber-600/80 backdrop-blur-sm rounded-full px-4 py-2 border border-white/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-white text-sm font-medium">Unknown user - Please position yourself properly</span>
                </div>
            </div>

            <canvas id="driverCanvas" class="hidden" width="2" height="2"></canvas>

            {{-- Emergency Alerts Overlay --}}
            <div id="emergencyAlerts" class="hidden absolute inset-0 z-20 flex flex-col items-center justify-center gap-4 bg-red-900/95 px-6 text-center">
                <div class="rounded-2xl border border-red-500/30 bg-red-950/80 p-6 max-w-sm">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-600/20 mb-4 mx-auto">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Emergency Alert</h3>
                    <p id="emergencyAlertMessage" class="text-sm text-red-200 mb-5">
                        You are in a potentially dangerous area. Please find a safe location before checking in or out.
                    </p>
                    <div class="space-y-2">
                        <button type="button" id="emergencyAlertAcknowledge" class="btn-primary w-full justify-center text-sm py-2.5">
                            I Understand, I'll Move to Safety
                        </button>
                        <button type="button" id="emergencyAlertProceed" class="btn-secondary w-full justify-center text-sm py-2.5">
                            Proceed Anyway (Emergency Only)
                        </button>
                    </div>
                </div>
            </div>

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
                        Allow <strong class="text-white">location</strong> so live tracking is automatically enabled for admin monitoring. For safety and better location accuracy, capture attendance only when stationary and away from unstable-signal road segments.
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
        const coverageGuide = document.getElementById('coverageGuide');
        const coverageGuideOkBtn = document.getElementById('coverageGuideOkBtn');
        const noFaceDetected = document.getElementById('noFaceDetected');
        const faceDetected = document.getElementById('faceDetected');
        const unknownFace = document.getElementById('unknownFace');
        const emergencyAlerts = document.getElementById('emergencyAlerts');
        const emergencyAlertMessage = document.getElementById('emergencyAlertMessage');
        const emergencyAlertAcknowledge = document.getElementById('emergencyAlertAcknowledge');
        const emergencyAlertProceed = document.getElementById('emergencyAlertProceed');

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
        let faceDetectionTimer = null;
        let emergencyAlertActive = false;
        const detector = ('FaceDetector' in window) ? new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 }) : null;

        // Face detection state management
        let lastFaceState = { detected: false, isKnown: false };
        let faceStateStabilityCount = 0;
        const FACE_STATE_STABILITY_THRESHOLD = 3; // Require 3 consistent detections before changing state

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
                startFaceDetection();
            } else {
                video.classList.add('hidden');
                previewImg.classList.remove('hidden');
                liveControls.classList.add('hidden');
                previewControls.classList.remove('hidden');
                if (faceDetectionTimer) {
                    clearInterval(faceDetectionTimer);
                    faceDetectionTimer = null;
                }
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
            if (faceDetectionTimer) {
                clearInterval(faceDetectionTimer);
                faceDetectionTimer = null;
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

        function updateFaceDetectionState(detected, isKnown = true) {
            if (!noFaceDetected || !faceDetected || !unknownFace) return;

            // Hide all states first to prevent simultaneous display
            noFaceDetected.classList.add('hidden');
            faceDetected.classList.add('hidden');
            unknownFace.classList.add('hidden');

            // Show only one appropriate state based on detection logic
            if (!detected) {
                // No face detected - show when camera is on but no face is present
                noFaceDetected.classList.remove('hidden');
            } else if (isKnown) {
                // Known user detected - real user/actual person
                faceDetected.classList.remove('hidden');
            } else {
                // Unknown user detected - different person, picture, or non-real user
                unknownFace.classList.remove('hidden');
            }
        }

        async function detectFace() {
            if (!video.videoWidth) return { detected: false, isKnown: false };

            const heuristic = () => {
                const w = 160;
                const h = 120;
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                ctx.drawImage(video, 0, 0, w, h);
                const image = ctx.getImageData(0, 0, w, h).data;
                let centerEnergy = 0;
                let outerEnergy = 0;
                let centerLum = 0;
                let centerCount = 0;
                let outerCount = 0;
                let motionPixels = 0;

                for (let y = 1; y < h - 1; y += 2) {
                    for (let x = 1; x < w - 1; x += 2) {
                        const idx = (y * w + x) * 4;
                        const g = (image[idx] * 0.299) + (image[idx + 1] * 0.587) + (image[idx + 2] * 0.114);
                        const right = (image[idx + 4] * 0.299) + (image[idx + 5] * 0.587) + (image[idx + 6] * 0.114);
                        const downIdx = ((y + 1) * w + x) * 4;
                        const down = (image[downIdx] * 0.299) + (image[downIdx + 1] * 0.587) + (image[downIdx + 2] * 0.114);
                        const edge = Math.abs(g - right) + Math.abs(g - down);
                        const inCenter = x > 42 && x < 118 && y > 26 && y < 94;

                        // Detect motion (changes in brightness)
                        if (Math.abs(g - 128) > 30) motionPixels++;

                        if (inCenter) {
                            centerEnergy += edge;
                            centerLum += g;
                            centerCount += 1;
                        } else {
                            outerEnergy += edge;
                            outerCount += 1;
                        }
                    }
                }

                const centerAvg = centerCount ? centerEnergy / centerCount : 0;
                const outerAvg = outerCount ? outerEnergy / outerCount : 0;
                const lumAvg = centerCount ? centerLum / centerCount : 0;
                const motionRatio = motionPixels / (w * h / 4); // Sampled pixels

                // Enhanced detection logic
                const hasEnoughLight = lumAvg > 35;
                const hasFaceFeatures = (centerAvg > 6 || outerAvg > 6);
                const hasGoodFaceStructure = hasFaceFeatures && lumAvg > 55 && lumAvg < 210;
                const hasMotion = motionRatio > 0.05; // Some motion indicates live person
                const isStaticImage = motionRatio < 0.02; // Very low motion might be a photo

                // Basic face detection
                const detected = hasEnoughLight && hasFaceFeatures;

                // Determine if it's a known/real user vs unknown/photo
                // Real users typically have: good structure + some motion + proper lighting
                // Photos might have: good structure but no motion
                // Unknown users might have: poor structure or wrong positioning
                const edgeBalance = outerAvg > 0 ? (centerAvg / outerAvg) : 1;
                const goodStructure = detected && edgeBalance > 0.82 && edgeBalance < 1.18 && hasGoodFaceStructure;

                let isKnown = false;
                if (detected) {
                    if (goodStructure && hasMotion) {
                        // Good structure + motion = likely real user
                        isKnown = true;
                    } else if (goodStructure && isStaticImage) {
                        // Good structure but no motion = might be a photo
                        isKnown = false;
                    } else {
                        // Poor structure = unknown user or bad positioning
                        isKnown = false;
                    }
                }

                return { detected, isKnown };
            };

            if (!detector) {
                return heuristic();
            }
            try {
                const faces = await detector.detect(video);
                if (!faces || !faces.length) return heuristic();
                const box = faces[0].boundingBox;
                const layout = (typeof window.polarisVideoFaceLayoutOnDisplay === 'function')
                    ? window.polarisVideoFaceLayoutOnDisplay(video, box)
                    : (function () {
                        const cx = box.x + (box.width / 2);
                        const cy = box.y + (box.height / 2);
                        return {
                            xRatio: cx / video.videoWidth,
                            yRatio: cy / video.videoHeight,
                            sizeRatio: box.width / video.videoWidth,
                        };
                    })();
                const { xRatio, yRatio, sizeRatio } = layout;
                const goodPosition = xRatio > 0.33 && xRatio < 0.67 && yRatio > 0.28 && yRatio < 0.63 && sizeRatio > 0.22 && sizeRatio < 0.56;

                // Use heuristic as backup for liveness detection
                const heuristicResult = heuristic();

                // Combine face detector position with heuristic liveness detection
                const isKnown = goodPosition && heuristicResult.isKnown;

                return { detected: true, isKnown };
            } catch (_e) {
                return heuristic();
            }
        }

        function startFaceDetection() {
            if (faceDetectionTimer) return;
            faceDetectionTimer = setInterval(async () => {
                if (mode !== 'live' || !stream) return;
                const currentState = await detectFace();

                // Implement state stability to prevent rapid flickering
                const stateChanged =
                    currentState.detected !== lastFaceState.detected ||
                    currentState.isKnown !== lastFaceState.isKnown;

                if (stateChanged) {
                    faceStateStabilityCount++;
                    if (faceStateStabilityCount >= FACE_STATE_STABILITY_THRESHOLD) {
                        // State has been stable for enough iterations, update it
                        lastFaceState = { ...currentState };
                        updateFaceDetectionState(currentState.detected, currentState.isKnown);
                        faceStateStabilityCount = 0;
                    }
                } else {
                    // State hasn't changed, reset counter
                    faceStateStabilityCount = 0;
                }
            }, 500);
        }

        function showEmergencyAlert(message) {
            if (emergencyAlertMessage) {
                emergencyAlertMessage.textContent = message;
            }
            if (emergencyAlerts) {
                emergencyAlerts.classList.remove('hidden');
            }
            emergencyAlertActive = true;
        }

        function hideEmergencyAlert() {
            if (emergencyAlerts) {
                emergencyAlerts.classList.add('hidden');
            }
            emergencyAlertActive = false;
        }

        function checkEmergencyAlerts(latitude, longitude) {
            // Check if emergency alerts are enabled in settings
            const emergencyAlertsEnabled = localStorage.getItem('emergencyAlertsEnabled') === 'true';
            if (!emergencyAlertsEnabled || !latitude || !longitude) return;

            // Get alert settings from localStorage (synced from database)
            const alertTypes = {
                unoperational: localStorage.getItem('emergencyAlertsUnoperational') === 'true',
                dangerous: localStorage.getItem('emergencyAlertsDangerous') === 'true',
                unreachable: localStorage.getItem('emergencyAlertsUnreachable') === 'true',
                low_data: localStorage.getItem('emergencyAlertsLowData') === 'true'
            };

            const alertRadius = parseFloat(localStorage.getItem('emergencyAlertsRadius') || '500') / 111000; // Convert meters to degrees

            // Simulate dangerous area detection (in real implementation, this would check against a database)
            // For demo purposes, we'll use some sample coordinates
            const dangerousAreas = [
                { lat: 14.6091, lng: 120.9769, type: 'unoperational', message: 'This area is currently unoperational. Please proceed with caution.' },
                { lat: 14.5995, lng: 120.9842, type: 'dangerous', message: 'High accident area detected. Please find an alternative route.' },
                { lat: 14.6150, lng: 120.9690, type: 'low_data', message: 'Low network connectivity area. GPS accuracy may be affected.' },
                { lat: 14.6050, lng: 120.9750, type: 'unreachable', message: 'This area is unreachable. Please find an alternative route.' },
            ];

            for (const area of dangerousAreas) {
                // Check if this alert type is enabled
                if (!alertTypes[area.type]) continue;

                const distance = Math.sqrt(
                    Math.pow(latitude - area.lat, 2) + Math.pow(longitude - area.lng, 2)
                );
                if (distance < alertRadius) {
                    showEmergencyAlert(area.message);

                    // Play sound if enabled
                    if (localStorage.getItem('emergencyAlertsSound') === 'true') {
                        playEmergencySound();
                    }

                    // Vibrate if enabled
                    if (localStorage.getItem('emergencyAlertsVibration') === 'true' && 'vibrate' in navigator) {
                        navigator.vibrate([200, 100, 200]);
                    }

                    return;
                }
            }
        }

        function playEmergencySound() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800; // Alert frequency
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (e) {
                console.log('Could not play emergency sound:', e);
            }
        }

        function syncEmergencyAlertSettings() {
            // Sync emergency alert settings from server to localStorage
            const settings = @json(\App\Models\Setting::whereIn('key', [
                'emergency_alerts_enabled',
                'emergency_alerts_unoperational',
                'emergency_alerts_dangerous',
                'emergency_alerts_unreachable',
                'emergency_alerts_low_data',
                'emergency_alerts_sound',
                'emergency_alerts_vibration',
                'emergency_alerts_radius'
            ])->pluck('value', 'key')->toArray());

            Object.keys(settings).forEach(key => {
                const storageKey = key.replace('emergency_alerts_', '');
                const camelCaseKey = storageKey.charAt(0).toUpperCase() + storageKey.slice(1);
                localStorage.setItem('emergencyAlerts' + camelCaseKey, settings[key]);
            });
        }

        function beginLiveLocationWatch() {
            if (!locationSharingEnabled || !navigator.geolocation) return;
            if (geoWatcherId !== null) return;

            geoWatcherId = navigator.geolocation.watchPosition(
                (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    document.getElementById('att_latitude').value = String(latitude);
                    document.getElementById('att_longitude').value = String(longitude);
                    if (position.coords.accuracy != null) {
                        document.getElementById('att_geo_accuracy').value = String(position.coords.accuracy);
                    }

                    // Check for emergency alerts
                    checkEmergencyAlerts(latitude, longitude);
                    const payload = JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        geo_accuracy: position.coords.accuracy,
                        speed: position.coords.speed ?? null,
                        heading: position.coords.heading ?? null,
                    });
                    const postLive = () =>
                        fetch(liveLocationEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: payload,
                        });
                    postLive()
                        .then(async (res) => {
                            if (res.status === 409) {
                                await fetch(enableLocationSharingEndpoint, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrf,
                                        'Accept': 'application/json',
                                    },
                                    credentials: 'same-origin',
                                });
                                return postLive();
                            }
                            return res;
                        })
                        .catch(() => {});
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
                            credentials: 'same-origin',
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
            // Initialize face detection state
            lastFaceState = { detected: false, isKnown: false };
            faceStateStabilityCount = 0;
            updateFaceDetectionState(false, false);
            try {
                setHint('Opening camera…');
                stream = await window.polarisRequestCameraOnly(video, { setHint });
                setHint('Position your face, then capture.');
                captureBtn.disabled = false;

                if (locationSharingEnabled) {
                    beginLiveLocationWatch();
                } else {
                    showLocationPermissionGate(
                        '<strong class="text-white">Location is off</strong> until you tap Enable location below. In limited-data or unoperational road areas, wait until GPS becomes stable before check in/check out.'
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
                if (!isAuto) setHint('Wait for camera preview to appear.');
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
                    // Always set to Asia/Manila timezone
                    capturedTimezoneInput.value = 'Asia/Manila';
                } catch (_e) {
                    capturedTimezoneInput.value = 'Asia/Manila';
                }
            }
            if (capturedTzOffsetInput) {
                // Calculate offset for Manila timezone (UTC+8)
                const manilaOffset = -8 * 60; // Manila is UTC+8, so offset is -480 minutes
                capturedTzOffsetInput.value = String(manilaOffset);
            }
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
        coverageGuideOkBtn?.addEventListener('click', () => {
            if (coverageGuide) {
                coverageGuide.style.display = 'none';
                coverageGuide.style.visibility = 'hidden';
                coverageGuide.style.opacity = '0';
                coverageGuide.style.pointerEvents = 'none';
            }
            // Store user preference to hide guide permanently with multiple fallbacks
            localStorage.setItem('coverageGuideAcknowledged', 'true');
            localStorage.setItem('coverageGuideAcknowledged', '1');
            // Also set a session storage backup
            sessionStorage.setItem('coverageGuideAcknowledged', 'true');
        });

        emergencyAlertAcknowledge?.addEventListener('click', () => {
            hideEmergencyAlert();
            setHint('Please move to a safe location before checking in or out.');
        });

        emergencyAlertProceed?.addEventListener('click', () => {
            hideEmergencyAlert();
            setHint('Proceed with caution. You are in an emergency situation.');
        });

        form?.addEventListener('submit', () => {
            if (capturedAtInput && !capturedAtInput.value) {
                capturedAtInput.value = new Date().toISOString();
            }
            if (capturedTimezoneInput && !capturedTimezoneInput.value) {
                // Always set to Asia/Manila timezone
                capturedTimezoneInput.value = 'Asia/Manila';
            }
            if (capturedTzOffsetInput && !capturedTzOffsetInput.value) {
                // Calculate offset for Manila timezone (UTC+8)
                const manilaOffset = -8 * 60; // Manila is UTC+8, so offset is -480 minutes
                capturedTzOffsetInput.value = String(manilaOffset);
            }
            submitting = true;
            submitBtn.disabled = true;
        });

        // Sync emergency alert settings from database
        syncEmergencyAlertSettings();

        // Check if coverage guide has been acknowledged and ensure it's properly hidden
        const coverageGuideAcknowledged = localStorage.getItem('coverageGuideAcknowledged');
        if (coverageGuideAcknowledged === 'true' || coverageGuideAcknowledged === '1') {
            if (coverageGuide) {
                coverageGuide.style.display = 'none';
                coverageGuide.style.visibility = 'hidden';
                coverageGuide.style.opacity = '0';
                coverageGuide.style.pointerEvents = 'none';
            }
        }

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
