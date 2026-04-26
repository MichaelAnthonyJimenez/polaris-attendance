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

            <div
                id="idvPreviewNav"
        </div>
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
