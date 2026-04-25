@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

<div
    id="idvShell"
    class="fixed inset-0 z-[100] flex flex-col bg-black text-white"
>
    <form method="POST" action="{{ route('driver-verification.store') }}" id="idVerificationForm" class="flex flex-1 flex-col min-h-0">
        @csrf
        <input type="hidden" name="verification_method" value="id_only">
        <input type="hidden" name="proof_mode" id="idv_proof_mode" value="selfie_with_id">
        <input type="hidden" name="id_front_base64" id="id_front_base64">
        <input type="hidden" name="id_back_base64" id="id_back_base64">
        <input type="hidden" name="face_selfie_base64" id="face_selfie_base64">
        <input type="hidden" name="latitude" id="idv_latitude">
        <input type="hidden" name="longitude" id="idv_longitude">
        <input type="hidden" name="geo_accuracy" id="idv_geo_accuracy">

        <header
            class="flex shrink-0 items-center gap-2 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 bg-gradient-to-b from-black/80 to-transparent"
            style="padding-left: max(0.75rem, env(safe-area-inset-left)); padding-right: max(0.75rem, env(safe-area-inset-right));"
        >
            <a
                href="{{ route('verification.required') }}"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white hover:bg-white/20 transition"
                aria-label="Back to verification options"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">Selfie with ID</p>
                <p id="idvHint" class="text-xs text-slate-400 truncate mt-0.5">Position your ID card in the frame.</p>
            </div>
        </header>

        <div class="flex-1 relative min-h-0 bg-black">
            <video
                id="idvVideo"
                class="absolute inset-0 h-full w-full object-cover"
                autoplay
                playsinline
                muted
            ></video>
            <img
                id="idvPreviewImg"
                src=""
                alt="Captured preview"
                class="absolute inset-0 hidden h-full w-full object-cover"
                width="1"
                height="1"
            />

            <div
                id="idvPreviewControls"
                class="hidden absolute inset-0 z-[6] flex items-center justify-between px-1 sm:px-3 pointer-events-none"
                aria-hidden="true"
            >
                <button
                    type="button"
                    id="idvPreviewPrev"
                    class="pointer-events-auto flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-full border border-white/25 bg-black/50 text-white shadow-lg backdrop-blur-sm transition hover:bg-black/65 disabled:opacity-25 disabled:pointer-events-none"
                    aria-label="Previous photo"
                >
                    <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button
                    type="button"
                    id="idvPreviewNext"
                    class="pointer-events-auto flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-full border border-white/25 bg-black/50 text-white shadow-lg backdrop-blur-sm transition hover:bg-black/65 disabled:opacity-25 disabled:pointer-events-none"
                    aria-label="Next photo"
                >
                    <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7"></path>
                    </svg>
                </button>
            </div>

            {{-- Guideline grid: ID card + face --}}
            <div id="idvGuide" class="pointer-events-none absolute inset-0 z-[5] flex items-center justify-center p-4" style="z-index: 5;">
                <div
                    class="relative rounded-2xl border-2 overflow-hidden"
                    style="width: 88%; max-width: 32rem; aspect-ratio: 4 / 3; border-color: rgba(255, 255, 255, 0.72); box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.42);"
                >
                    <svg
                        id="idvGridSvg"
                        class="absolute inset-0 h-full w-full"
                        viewBox="0 0 100 75"
                        preserveAspectRatio="none"
                        style="color: rgba(255,255,255,0.52);"
                        aria-hidden="true"
                    >
                        <!-- ID card frame -->
                        <rect x="10" y="5" width="35" height="22" fill="none" stroke="rgba(59, 130, 246, 0.5)" stroke-width="0.5" stroke-dasharray="2 1"/>
                        <text x="27.5" y="3" fill="rgba(59, 130, 246, 0.8)" font-size="2" text-anchor="middle" font-weight="600">ID CARD</text>

                        <!-- Face frame -->
                        <rect x="55" y="20" width="35" height="45" fill="none" stroke="rgba(34, 197, 94, 0.5)" stroke-width="0.5" stroke-dasharray="2 1"/>
                        <text x="72.5" y="18" fill="rgba(34, 197, 94, 0.8)" font-size="2" text-anchor="middle" font-weight="600">FACE</text>
                    </svg>
                    <div class="absolute top-2 right-2 rounded-lg bg-black/45 px-2 py-1 text-[10px] sm:text-xs font-medium text-white/90">
                        <span class="inline-flex items-center gap-1.5 mr-2">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-green-500"></span> Proper
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span> Improper
                        </span>
                    </div>
                    <div class="absolute bottom-2 left-0 right-0 text-center px-2"></div>
                </div>
            </div>

            <canvas id="idvCanvas" class="hidden" width="2" height="2"></canvas>

            <div
                id="idvPermissionGate"
                class="hidden absolute inset-0 z-10 flex flex-col items-center justify-center gap-4 bg-black/90 px-6 text-center"
            >
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 max-w-sm">
                    <p class="text-base font-semibold text-white mb-2">Camera access</p>
                    <p id="idvPermissionText" class="text-sm text-slate-300 mb-5">
                        We need your camera for verification. When your browser asks, choose <strong class="text-white">Allow</strong> for camera.
                    </p>
                    <button type="button" id="idvRetryBtn" class="btn-primary w-full justify-center text-sm py-2.5">
                        Enable camera
                    </button>
                </div>
            </div>
            <div id="idvCountdown" class="hidden absolute inset-0 z-[11] items-center justify-center pointer-events-none">
                <div id="idvCountdownNumber" class="h-20 w-20 rounded-full border-2 border-white/60 bg-black/45 text-3xl font-bold flex items-center justify-center">3</div>
            </div>
        </div>

                    <div class="hidden space-y-3">
                        <button type="button" id="idvRetakeBtn" class="btn-secondary w-full py-3 text-sm">
                            Retake
                        </button>

                        <button type="submit" id="idvSubmit" class="btn-primary w-full py-3 text-sm hidden">
                            Submit verification
                        </button>
                    </div>
                </div>

                <!-- Countdown overlay -->
                <div id="idvCountdown" class="hidden absolute inset-0 z-[11] items-center justify-center pointer-events-none">
                    <div id="idvCountdownNumber" class="h-20 w-20 rounded-full border-2 border-white/60 bg-black/45 text-3xl font-bold flex items-center justify-center">3</div>
                </div>
            </div>
        </div>

        {{-- Footer with controls --}}
        <footer
            id="idvSelfieFooter"
            class="shrink-0 flex flex-col items-center gap-4 px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] bg-gradient-to-t from-black via-black/95 to-transparent"
            style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));"
        >
            <div class="flex items-center gap-3 text-xs text-slate-400">
                <span id="idvStepTitle">Step 1 of 2: Position ID</span>
            </div>
        </footer>
    </form>
</div>

<!-- OCR Confirmation Script -->
<script src="{{ asset('js/optiic-service.js') }}"></script>
<script>
(() => {
    const LS_AUTO = 'idv_auto_capture';
    const video = document.getElementById('idvVideo');
    const previewImg = document.getElementById('idvPreviewImg');
    const canvas = document.getElementById('idvCanvas');
    const captureBtn = document.getElementById('idvCapture');
    const retakeBtn = document.getElementById('idvRetakeBtn');
    const submitBtn = document.getElementById('idvSubmit');
    const hint = document.getElementById('idvHint');
    const stepTitle = document.getElementById('idvStepTitle');
    const guide = document.getElementById('idvGuide');
    const autoBtn = document.getElementById('idvAutoCaptureToggle');
    const cameraToggleBtn = document.getElementById('idvCameraToggle');
    const liveControls = document.getElementById('idvLiveControls');
    const previewControls = document.getElementById('idvPreviewControls');
    const countdownWrap = document.getElementById('idvCountdown');
    const countdownNumber = document.getElementById('idvCountdownNumber');

    const inputs = {
        front: document.getElementById('id_front_base64'),
        selfie: document.getElementById('face_selfie_base64'),
    };

    let stream = null;
    let mode = 'live';
    let stepIndex = 0;
    let autoCapture = localStorage.getItem('idv_auto_capture') === '1';
    let cameraFacingMode = 'environment';
    let countdownTimer = null;
    let autoCaptureQueued = false;
    let alignmentGood = false;
    let alignCheckTimer = null;
    const steps = [
        { key: 'front', label: 'ID card front', title: 'Step 1 of 2: Capture ID front', hint: 'Hold the front side of your ID inside the rectangle frame.', guide: 'id' },
        { key: 'selfie', label: 'Selfie with ID', title: 'Step 2 of 2: Capture selfie with ID', hint: 'Keep your face in the circle and ID visible in frame.', guide: 'face' },
    ];

    function setHint(text) {
        if (hint) hint.textContent = text;
    }

    function setMode(newMode) {
        mode = newMode;
        if (mode === 'live') {
            video.classList.remove('hidden');
            previewImg.classList.add('hidden');
            captureBtn.classList.remove('hidden');
            retakeBtn.classList.add('hidden');
        } else {
            video.classList.add('hidden');
            previewImg.classList.remove('hidden');
            captureBtn.classList.add('hidden');
            retakeBtn.classList.remove('hidden');
        }
    }

    function syncStepUi() {
        const step = steps[stepIndex];
        if (!step) return;
        if (stepTitle) stepTitle.textContent = step.title;
        if (hint) setHint(step.hint);
    }

    function syncAutoUi() {
        if (!autoBtn) return;
        autoBtn.textContent = autoCapture ? 'Auto: on' : 'Auto: off';
        autoBtn.setAttribute('aria-pressed', autoCapture ? 'true' : 'false');
    }

    function syncCameraUi() {
        if (!cameraToggleBtn) return;
        const isRear = cameraFacingMode === 'environment';
        cameraToggleBtn.textContent = isRear ? 'Rear' : 'Front';
        cameraToggleBtn.setAttribute('aria-pressed', isRear ? 'true' : 'false');
    }

    function clearCountdown() {
        if (countdownTimer) {
            window.clearInterval(countdownTimer);
            countdownTimer = null;
        }
        autoCaptureQueued = false;
        if (countdownWrap) countdownWrap.classList.add('hidden');
    }

    function runAutoCountdown(onDone) {
        clearCountdown();
        autoCaptureQueued = true;
        let remaining = 3;
        if (countdownNumber) countdownNumber.textContent = String(remaining);
        if (countdownWrap) {
            countdownWrap.classList.remove('hidden');
            countdownWrap.classList.add('flex');
        }

        countdownTimer = window.setInterval(() => {
            remaining -= 1;
            if (countdownNumber) countdownNumber.textContent = String(remaining);
            if (remaining <= 0) {
                autoCaptureQueued = false;
                clearCountdown();
                onDone();
                return;
            }
        }, 1000);
    }

    function queueAutoCapture() {
        if (!autoCapture || autoCaptureQueued || !alignmentGood) return;
        runAutoCountdown(() => captureFrame(true));
    }

    function stopAlignmentLoop() {
        if (alignCheckTimer) {
            window.clearInterval(alignCheckTimer);
            alignCheckTimer = null;
        }
    }

    function startAlignmentLoop() {
        stopAlignmentLoop();
        alignCheckTimer = window.setInterval(async () => {
            if (mode !== 'live' || !stream) return;
            // Simple alignment check - in real implementation, use face detection
            alignmentGood = true; // Simplified for now

            if (!alignmentGood) {
                if (autoCaptureQueued) {
                    clearCountdown();
                }
                return;
            }
            if (autoCapture && !autoCaptureQueued && video.videoWidth) {
                queueAutoCapture();
            }
        }, 350);
    }

    function refreshSubmit() {
        const hasFront = inputs.front.value && inputs.front.value.length > 0;
        const hasSelfie = inputs.selfie.value && inputs.selfie.value.length > 0;

        if (hasFront && hasSelfie) {
            submitBtn.classList.remove('hidden');
            setHint('All photos captured. You can now submit.');
        } else if (hasFront) {
            setHint('ID captured. Now capture selfie with ID.');
        } else {
            setHint('Position your ID in the frame and capture.');
        }
    }

    async function startCamera() {
        try {
            const constraints = {
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 960 }
                }
            };

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            setHint('Camera ready. Position your ID in the frame.');
        } catch (error) {
            console.error('Camera error:', error);
            setHint('Camera access denied or not available.');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    function captureFrame(isAuto = false) {
        if (!video.videoWidth) {
            if (!isAuto) setHint('Start the camera first.');
            return;
        }

        const ctx = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

        const step = steps[stepIndex];
        if (!step) return;

        if (step.key === 'front') inputs.front.value = dataUrl;
        if (step.key === 'selfie') inputs.selfie.value = dataUrl;

        refreshSubmit();

        if (stepIndex < steps.length - 1) {
            stepIndex += 1;
            syncStepUi();
            if (!autoCapture) {
                setHint('Saved ' + step.label + '. Continue to ' + steps[stepIndex].label + '.');
            }
            return;
        }

        previewImg.src = dataUrl;
        setMode('preview');
        setHint('All photos captured. You can now submit.');
    }

    // Event listeners
    captureBtn?.addEventListener('click', () => captureFrame());

    retakeBtn?.addEventListener('click', () => {
        previewImg.removeAttribute('src');
        stepIndex = 0;
        inputs.front.value = '';
        inputs.selfie.value = '';
        setMode('live');
        syncStepUi();
        refreshSubmit();
    });

    document.getElementById('idvPreviewPrev')?.addEventListener('click', () => {
        if (stepIndex > 0) {
            stepIndex -= 1;
            syncStepUi();
            setMode('live');
            startCamera();
        }
    });

    document.getElementById('idvPreviewNext')?.addEventListener('click', () => {
        if (stepIndex < steps.length - 1) {
            stepIndex += 1;
            syncStepUi();
            setMode('live');
            startCamera();
        }
    });

    autoBtn?.addEventListener('click', () => {
        autoCapture = !autoCapture;
        localStorage.setItem('idv_auto_capture', autoCapture ? '1' : '0');
        syncAutoUi();
    });

    cameraToggleBtn?.addEventListener('click', async () => {
        cameraFacingMode = cameraFacingMode === 'environment' ? 'user' : 'environment';
        syncCameraUi();
        if (mode === 'live') {
            await startCamera();
        }
    });

    // Initialize
    syncStepUi();
    syncAutoUi();
    syncCameraUi();
    startCamera();
    startAlignmentLoop();
    window.addEventListener('beforeunload', stopCamera);
})();
</script>
@endsection
