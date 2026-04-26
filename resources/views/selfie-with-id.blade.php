@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

<div
    id="idvShell"
    class="fixed inset-0 z-[100] flex flex-col bg-black text-white"
>
    <form id="selfieWithIdForm" method="POST" action="{{ route('selfie-with-id.store') }}" class="flex flex-1 flex-col min-h-0">
        @csrf
        <input type="hidden" name="verification_method" value="id_only">
        <input type="hidden" name="proof_mode" value="selfie_with_id">
        <input type="hidden" name="id_front_base64" id="idv_id_front_base64" value="">
        <input type="hidden" name="face_selfie_base64" id="idv_face_selfie_base64" value="">
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
                <p class="text-sm font-semibold text-white truncate">Selfie with ID verification</p>
                <p id="idvHint" class="text-xs text-slate-400 truncate mt-0.5">
                    Allow camera, align with the grid, then capture: ID card, selfie with ID.
                </p>
            </div>
            <button
                type="button"
                id="idvAutoCaptureToggle"
                class="shrink-0 flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold transition border-white/20 bg-white/10 text-white"
                aria-pressed="false"
                aria-label="Toggle auto capture"
                title="Auto capture when each step is ready"
            >
                <span class="relative flex h-2 w-2">
                    <span class="h-2 w-2 rounded-full bg-red-500" aria-hidden="true"></span>
                </span>
                <span id="idvAutoCaptureLabel">Auto: off</span>
            </button>
        </header>

        <div class="flex-1 relative bg-black overflow-hidden" style="padding-bottom: env(safe-area-inset-bottom);">
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

            <!-- Camera guides and overlays -->
            <div class="absolute inset-0 pointer-events-none">
                <svg id="idvGridSvg" class="absolute inset-0 w-full h-full" viewBox="0 0 400 300" style="color: rgba(255,255,255,0.3);">
                    <!-- Grid lines will be dynamically added -->
                </svg>
                <div id="idvGuide" class="absolute inset-0 flex items-center justify-center">
                    <div id="idvGuideFrame" class="w-64 h-40 border-2 border-dashed rounded-lg transition-colors duration-200" style="border-color: rgba(255,255,255,0.6);"></div>
                </div>
                <div id="idvGuideHint" class="absolute top-4 left-0 right-0 text-center text-xs text-white/80"></div>
                <div id="idvGridTint" class="absolute inset-0 transition-opacity duration-200" style="opacity: 0; background: rgba(34, 197, 94, 0.2);"></div>
            </div>

            <!-- Permission gate -->
            <div id="idvPermissionGate" class="hidden absolute inset-0 z-[200] flex items-center justify-center bg-black/90">
                <div class="text-center px-6 py-8 max-w-sm">
                    <p class="text-white text-lg font-semibold mb-2">Camera Access Required</p>
                    <p id="idvPermissionText" class="text-white/80 text-sm mb-4">Please allow camera access to continue with verification.</p>
                    <button id="idvRetryBtn" type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        Allow Camera
                    </button>
                </div>
            </div>

            <!-- Countdown overlay -->
            <div id="idvCountdown" class="hidden absolute inset-0 z-[150] flex items-center justify-center bg-black/50">
                <div id="idvCountdownNumber" class="w-16 h-16 rounded-full border-2 border-white bg-black/60 text-white text-2xl font-bold flex items-center justify-center">
                    3
                </div>
            </div>

            <!-- Live camera controls -->
            <div id="idvLiveControls" class="absolute bottom-20 left-0 right-0 flex flex-col items-center gap-4 px-4" style="padding-bottom: max(2rem, env(safe-area-inset-bottom));">
                <div class="text-center mb-2">
                    <p id="idvStepTitle" class="text-white text-sm font-semibold">Step 1 of 2: Capture ID card</p>
                </div>
                <div class="flex items-center gap-4">
                    <button id="idvCameraToggleBtn" type="button" class="bg-white/20 hover:bg-white/30 text-white p-3 rounded-full transition-colors" aria-label="Switch camera">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                    <button id="idvCaptureBtn" type="button" class="bg-white hover:bg-white/90 text-black w-16 h-16 rounded-full flex items-center justify-center transition-all transform hover:scale-105 active:scale-95">
                        <div class="w-14 h-14 rounded-full border-4 border-black/20"></div>
                    </button>
                </div>
            </div>

            <!-- Preview controls -->
            <div id="idvPreviewControls" class="hidden absolute bottom-20 left-0 right-0 flex flex-col items-center gap-4 px-4" style="padding-bottom: max(2rem, env(safe-area-inset-bottom));">
                <div class="text-center mb-2">
                    <p class="text-white text-sm font-semibold">Review Capture</p>
                </div>
                <div class="flex items-center gap-3">
                    <button id="idvRetakeBtn" type="button" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Retake
                    </button>
                    <button id="idvSubmitBtn" type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        Submit
                    </button>
                </div>
            </div>

            <!-- Step thumbnails -->
            <div id="idvStepThumbsRow" class="hidden absolute top-4 right-4 flex gap-2">
                <div class="relative">
                    <img id="idvStepPreviewFront" class="hidden w-12 h-12 rounded border-2 border-white/50 object-cover" alt="ID card">
                    <div id="idvStepPlaceholderFront" class="w-12 h-12 rounded border-2 border-dashed border-white/30 flex items-center justify-center">
                        <span class="text-white/60 text-xs">ID</span>
                    </div>
                </div>
                <div class="relative">
                    <img id="idvStepPreviewSelfie" class="hidden w-12 h-12 rounded border-2 border-white/50 object-cover" alt="Selfie with ID">
                    <div id="idvStepPlaceholderSelfie" class="w-12 h-12 rounded border-2 border-dashed border-white/30 flex items-center justify-center">
                        <span class="text-white/60 text-xs">📸</span>
                    </div>
                </div>
            </div>

            <!-- Canvas for capture -->
            <canvas id="idvCanvas" class="hidden"></canvas>
        </div>
</div>

<script>
function startSelfieWithId() {
    // Redirect to the actual selfie with ID camera page
    window.location.href = '{{ route('verification.selfie-camera') }}';
}
</script>
@endsection

<script>
(() => {
    const LS_AUTO = 'idv_auto_capture';
    const video = document.getElementById('idvVideo');
    const previewImg = document.getElementById('idvPreviewImg');
    const canvas = document.getElementById('idvCanvas');
    const captureBtn = document.getElementById('idvCaptureBtn');
    const retakeBtn = document.getElementById('idvRetakeBtn');
    const submitBtn = document.getElementById('idvSubmitBtn');
    const hint = document.getElementById('idvHint');
    const stepTitle = document.getElementById('idvStepTitle');
    const stepThumbsRow = document.getElementById('idvStepThumbsRow');
    const liveControls = document.getElementById('idvLiveControls');
    const previewControls = document.getElementById('idvPreviewControls');
    const previewPrev = document.getElementById('idvPreviewPrev');
    const previewNext = document.getElementById('idvPreviewNext');
    const autoBtn = document.getElementById('idvAutoCaptureToggle');
    const autoLabel = document.getElementById('idvAutoCaptureLabel');
    const countdownWrap = document.getElementById('idvCountdown');
    const countdownNumber = document.getElementById('idvCountdownNumber');
    const gridSvg = document.getElementById('idvGridSvg');
    const gridTint = document.getElementById('idvGridTint');
    const guide = document.getElementById('idvGuide');
    const guideFrame = document.getElementById('idvGuideFrame');
    const guideHint = document.getElementById('idvGuideHint');
    const cameraToggleBtn = document.getElementById('idvCameraToggleBtn');

    const inputs = {
        front: document.getElementById('idv_id_front_base64'),
        selfie: document.getElementById('idv_face_selfie_base64'),
    };

    // Step definitions
    const steps = [
        { key: 'front', label: 'ID card', title: 'Step 1 of 2: Capture ID card', hint: 'Hold your ID card inside the frame.', guide: 'id' },
        { key: 'selfie', label: 'Selfie with ID', title: 'Step 2 of 2: Capture selfie with ID', hint: 'Hold your ID card and face in the frame.', guide: 'face' },
    ];

    let stepIndex = 0;
    let mode = 'live'; // 'live' or 'preview'
    let stream = null;
    let cameraFacingMode = 'environment';
    let autoCapture = localStorage.getItem(LS_AUTO) === '1';
    let autoCaptureQueued = false;
    let countdownTimer = null;
    let alignmentCheckTimer = null;
    let alignmentGood = false;

    function setHint(text) {
        if (hint) hint.textContent = text;
    }

    function setMode(newMode) {
        mode = newMode;
        if (newMode === 'live') {
            video.classList.remove('hidden');
            previewImg.classList.add('hidden');
            previewPrev.classList.add('hidden');
            previewNext.classList.add('hidden');
            liveControls.classList.remove('hidden');
            previewControls.classList.add('hidden');
        } else {
            video.classList.add('hidden');
            previewImg.classList.remove('hidden');
            previewPrev.classList.remove('hidden');
            previewNext.classList.remove('hidden');
            liveControls.classList.add('hidden');
            previewControls.classList.remove('hidden');
        }
    }

    function syncStepUi() {
        const step = steps[stepIndex];
        if (!step) return;
        if (stepTitle) stepTitle.textContent = step.title;
        if (hint) setHint('Current: ' + step.label);

        // Update guide based on step
        updateGuideForStep(step);
    }

    function updateGuideForStep(step) {
        if (!guide || !gridSvg || !guideHint) return;

        if (step.guide === 'id') {
            // ID card guide - show ID card frame
            gridSvg.innerHTML = `
                <rect x="50" y="40" width="300" height="170" fill="none" stroke="rgba(59, 130, 246, 0.8)" stroke-width="3" stroke-dasharray="8 4"/>
                <text x="200" y="25" fill="rgba(59, 130, 246, 0.9)" font-size="18" text-anchor="middle" font-weight="600">ID CARD</text>
            `;
            guideHint.textContent = 'Position ID card in frame';
            if (guideFrame) {
                guideFrame.style.borderColor = 'rgba(59, 130, 246, 0.8)';
            }
        } else {
            // Selfie with ID guide - show face + ID
            gridSvg.innerHTML = `
                <circle cx="200" cy="125" r="50" fill="none" stroke="rgba(34, 197, 94, 0.8)" stroke-width="3" stroke-dasharray="8 4"/>
                <text x="200" y="65" fill="rgba(34, 197, 94, 0.9)" font-size="18" text-anchor="middle" font-weight="600">FACE</text>
                <rect x="80" y="180" width="120" height="50" fill="none" stroke="rgba(59, 130, 246, 0.8)" stroke-width="3" stroke-dasharray="8 4"/>
                <text x="140" y="210" fill="rgba(59, 130, 246, 0.9)" font-size="14" text-anchor="middle" font-weight="600">ID</text>
            `;
            guideHint.textContent = 'Hold ID card and face in frame';
            if (guideFrame) {
                guideFrame.style.borderColor = 'rgba(34, 197, 94, 0.8)';
            }
        }
    }

    function refreshSubmit() {
        const hasFront = inputs.front.value && inputs.front.value.length > 0;
        const hasSelfie = inputs.selfie.value && inputs.selfie.value.length > 0;
        const hasAll = hasFront && hasSelfie;

        if (submitBtn) {
            submitBtn.disabled = !hasAll;
            submitBtn.classList.toggle('hidden', !hasAll);
        }

        // Update step thumbnails
        updateStepThumbnails();
    }

    function updateStepThumbnails() {
        const frontThumb = document.getElementById('idvStepPreviewFront');
        const selfieThumb = document.getElementById('idvStepPreviewSelfie');
        const frontPlaceholder = document.getElementById('idvStepPlaceholderFront');
        const selfiePlaceholder = document.getElementById('idvStepPlaceholderSelfie');

        if (frontThumb && inputs.front.value) {
            frontThumb.src = inputs.front.value;
            frontThumb.classList.remove('hidden');
            frontPlaceholder.classList.add('hidden');
        }

        if (selfieThumb && inputs.selfie.value) {
            selfieThumb.src = inputs.selfie.value;
            selfieThumb.classList.remove('hidden');
            selfiePlaceholder.classList.add('hidden');
        }

        // Show thumbnails row when we have at least one capture
        if (stepThumbsRow && (inputs.front.value || inputs.selfie.value)) {
            stepThumbsRow.classList.remove('hidden');
        }
    }

    // Camera functions
    async function startCamera() {
        try {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            setHint('Opening camera…');

            const constraints = {
                video: {
                    facingMode: cameraFacingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;

            // Hide permission gate when successful
            const permissionGate = document.getElementById('idvPermissionGate');
            if (permissionGate) {
                permissionGate.classList.add('hidden');
            }

            // Start alignment checking
            startAlignmentLoop();
        } catch (error) {
            console.error('Camera error:', error);
            captureBtn.disabled = true;

            // Show permission gate when access is denied
            const permissionGate = document.getElementById('idvPermissionGate');
            if (permissionGate) {
                permissionGate.classList.remove('hidden');
            }

            // Update permission text to match facial verification
            const permissionText = document.getElementById('idvPermissionText');
            if (permissionText) {
                permissionText.innerHTML = 'We could not use the camera. Allow camera access in your browser settings, or tap below to try again.';
            }

            setHint('Camera not available.');
        }
    }

    // Screen size capture function
    window.polarisRequestCameraOnly = async function (videoEl, options) {
        if (!videoEl) {
            throw new Error('No video element');
        }
        var setHint =
            options && typeof options.setHint === 'function'
                ? options.setHint
                : function () {};

        var gum =
            navigator.mediaDevices &&
            typeof navigator.mediaDevices.getUserMedia === 'function'
                ? navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices)
                : null;

        if (!gum && typeof navigator.webkitGetUserMedia === 'function') {
            gum = function (constraints) {
                return new Promise(function (resolve, reject) {
                    navigator.webkitGetUserMedia(constraints, resolve, reject);
                });
            };
        }

        if (!gum) {
            setHint('Camera is not supported in this browser.');
            throw new Error('getUserMedia not available');
        }

        try {
            setHint('Opening camera…');
            var constraints = {
                video: {
                    facingMode: cameraFacingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            var stream = await gum(constraints);
            videoEl.srcObject = stream;

            // Hide permission gate when successful
            const permissionGate = document.getElementById('idvPermissionGate');
            if (permissionGate) {
                permissionGate.classList.add('hidden');
            }

            return stream;
        } catch (error) {
            console.error('Camera error:', error);
            captureBtn.disabled = true;

            // Show permission gate when access is denied
            const permissionGate = document.getElementById('idvPermissionGate');
            if (permissionGate) {
                permissionGate.classList.remove('hidden');
            }

            // Update permission text to match facial verification
            const permissionText = document.getElementById('idvPermissionText');
            if (permissionText) {
                permissionText.innerHTML = 'We could not use the camera. Allow camera access in your browser settings, or tap below to try again.';
            }

            setHint('Camera not available.');
            throw error;
        }
    };

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    // Capture functions
    function captureFrame(isAuto = false) {
        if (!video || !video.videoWidth || !video.videoHeight) return;

        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
        const step = steps[stepIndex];
        if (!step) return;

        // Store the captured image
        if (step.key === 'front') {
            inputs.front.value = dataUrl;
        } else if (step.key === 'selfie') {
            inputs.selfie.value = dataUrl;
        }

        // Update preview
        previewImg.src = dataUrl;
        setMode('preview');
        refreshSubmit();

        // Move to next step or finish
        if (stepIndex < steps.length - 1) {
            stepIndex += 1;
            syncStepUi();
            if (!autoCapture) {
                setHint('Saved ' + step.label + '. Continue to ' + steps[stepIndex].label + '.');
            }
        } else {
            setHint('All captures complete. Review and submit.');
            submitBtn.classList.remove('hidden');
        }
    }

    // Auto capture functions
    function syncAutoUi() {
        if (!autoBtn) return;
        autoLabel.textContent = autoCapture ? 'Auto: on' : 'Auto: off';
        autoBtn.setAttribute('aria-pressed', autoCapture ? 'true' : 'false');
    }

    function queueAutoCapture() {
        if (!autoCapture || autoCaptureQueued || !alignmentGood) return;
        runAutoCountdown(() => captureFrame(true));
    }

    function runAutoCountdown(callback) {
        if (countdownTimer) return;

        autoCaptureQueued = true;
        let count = 3;

        countdownWrap.classList.remove('hidden');
        countdownNumber.textContent = count;

        countdownTimer = setInterval(() => {
            count--;
            if (count > 0) {
                countdownNumber.textContent = count;
            } else {
                clearInterval(countdownTimer);
                countdownTimer = null;
                countdownWrap.classList.add('hidden');
                callback();
                autoCaptureQueued = false;
            }
        }, 1000);
    }

    function clearCountdown() {
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
        countdownWrap.classList.add('hidden');
        autoCaptureQueued = false;
    }

    // Alignment checking
    function startAlignmentLoop() {
        stopAlignmentLoop();
        alignmentCheckTimer = setInterval(() => {
            // Simple alignment check - in real implementation, this would use face detection
            alignmentGood = Math.random() > 0.3; // Simulated alignment
            if (alignmentGood) {
                gridTint.style.opacity = '1';
                queueAutoCapture();
            } else {
                gridTint.style.opacity = '0';
            }
        }, 500);
    }

    function stopAlignmentLoop() {
        if (alignmentCheckTimer) {
            clearInterval(alignmentCheckTimer);
            alignmentCheckTimer = null;
        }
    }

    // Event listeners
    captureBtn?.addEventListener('click', () => captureFrame());

    retakeBtn?.addEventListener('click', () => {
        previewImg.removeAttribute('src');
        setMode('live');
        syncStepUi();
        refreshSubmit();
    });

    previewPrev?.addEventListener('click', () => {
        if (stepIndex > 0) {
            stepIndex -= 1;
            syncStepUi();
            setMode('live');
            startCamera();
        }
    });

    previewNext?.addEventListener('click', () => {
        if (stepIndex < steps.length - 1) {
            stepIndex += 1;
            syncStepUi();
            setMode('live');
            startCamera();
        }
    });

    autoBtn?.addEventListener('click', () => {
        autoCapture = !autoCapture;
        localStorage.setItem(LS_AUTO, autoCapture ? '1' : '0');
        syncAutoUi();
    });

    cameraToggleBtn?.addEventListener('click', async () => {
        cameraFacingMode = cameraFacingMode === 'environment' ? 'user' : 'environment';
        cameraToggleBtn.setAttribute('aria-pressed', cameraFacingMode === 'environment' ? 'true' : 'false');
        if (mode === 'live') {
            await startCamera();
        }
    });

    // Permission gate retry button
    const retryBtn = document.getElementById('idvRetryBtn');
    retryBtn?.addEventListener('click', async () => {
        const permissionGate = document.getElementById('idvPermissionGate');
        if (permissionGate) {
            permissionGate.classList.add('hidden');
        }
        await startCamera();
    });

    // Initialize
    syncStepUi();
    refreshSubmit();

    // Start camera automatically when page loads
    startCamera();
})();
</script>
@endsection
