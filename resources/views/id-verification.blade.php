@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

<div
    id="idvShell"
    class="fixed inset-0 z-[2147483647] flex flex-col bg-black text-white"
    style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100vw; height: 100vh;"
>
    <form method="POST" action="{{ route('driver-verification.store') }}" id="idVerificationForm" class="flex flex-1 flex-col min-h-0">
            @csrf
        <input type="hidden" name="verification_method" value="id_only">
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
                <p class="text-sm font-semibold text-white truncate">ID verification</p>
                <p id="idvHint" class="text-xs text-slate-400 truncate mt-0.5">
                    Allow camera, then capture ID front and selfie with ID.
                </p>
                </div>
            <button type="button" id="idvAutoCaptureToggle" class="btn-secondary text-xs px-3 py-2" aria-pressed="false">Auto: off</button>
        </header>

        <div class="flex-1 relative min-h-0 bg-black">
            <video id="idvVideo" class="absolute inset-0 h-full w-full object-cover" autoplay playsinline muted></video>
            <img
                id="idvPreviewImg"
                src=""
                alt="Captured preview"
                class="absolute inset-0 hidden h-full w-full object-cover"
                width="1"
                height="1"
            />
            <canvas id="idvCanvas" class="hidden"></canvas>

            <div id="idvGuide" class="pointer-events-none absolute inset-0 z-[5] flex items-center justify-center p-4" style="z-index: 5;">
                <div class="relative rounded-2xl border-2 overflow-hidden" style="width: 90%; max-width: 36rem; aspect-ratio: 3 / 4; border-color: rgba(255, 255, 255, 0.78); box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.42);">
                    <svg
                        id="idvGridSvg"
                        class="absolute inset-0 h-full w-full"
                        viewBox="0 0 300 400"
                        preserveAspectRatio="none"
                        style="color: rgba(255,255,255,0.52);"
                        aria-hidden="true"
                    >
                        <line x1="100" y1="0" x2="100" y2="400" stroke="currentColor" stroke-width="1.6" />
                        <line x1="200" y1="0" x2="200" y2="400" stroke="currentColor" stroke-width="1.6" />
                        <line x1="0" y1="133.3" x2="300" y2="133.3" stroke="currentColor" stroke-width="1.6" />
                        <line x1="0" y1="266.6" x2="300" y2="266.6" stroke="currentColor" stroke-width="1.6" />
                    </svg>
                    <div id="idvGuideIdFrame" class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                        <div id="idvIdZone" class="rounded-xl border-[3px] border-dashed" style="width: 82%; max-width: 360px; aspect-ratio: 1.6 / 1; border-color: rgba(255,255,255,0.86); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.32) inset;"></div>
                    </div>
                    <div id="idvGuideFaceFrame" class="absolute inset-0 hidden items-center justify-center" aria-hidden="true">
                        <div id="idvFaceZone" class="rounded-full border-[3px] border-dashed" style="width: 62%; max-width: 220px; aspect-ratio: 1 / 1; border-color: rgba(255,255,255,0.86); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.32) inset;"></div>
                    </div>
                    <div id="idvGridTint" class="absolute inset-0 transition-opacity duration-200" style="opacity: 0; background: rgba(34, 197, 94, 0.22);"></div>
                    <div class="absolute top-2 right-2 rounded-lg bg-black/45 px-2 py-1 text-[10px] sm:text-xs font-medium text-white/90">
                        <span class="inline-flex items-center gap-1.5 mr-2">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-green-500"></span> Proper
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span> Improper
                        </span>
                    </div>
                    <div class="absolute bottom-2 left-0 right-0 text-center px-2">
                        <span id="idvGuideLabel" class="text-[10px] sm:text-xs font-medium text-white/90 drop-shadow-md">Align ID front in the card frame</span>
                    </div>
                </div>
                </div>

            <div id="idvCountdown" class="hidden absolute inset-0 z-[11] items-center justify-center pointer-events-none">
                <div id="idvCountdownNumber" class="h-20 w-20 rounded-full border-2 border-white/60 bg-black/45 text-3xl font-bold flex items-center justify-center">3</div>
                </div>

            <div
                id="idvPermissionGate"
                class="hidden absolute inset-0 z-[210] flex flex-col items-center justify-center gap-4 bg-black/90 px-6 text-center"
            >
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 max-w-sm">
                    <p class="text-base font-semibold text-white mb-2">Camera access</p>
                    <p id="idvPermissionText" class="text-sm text-slate-300 mb-5">
                        We need camera access for ID verification. Please allow camera when prompted.
                    </p>
                    <button type="button" id="idvEnableBtn" class="btn-primary w-full justify-center text-sm py-2.5">Continue</button>
                </div>
            </div>
        </div>

        <footer
            class="shrink-0 flex flex-col items-center gap-4 px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] bg-gradient-to-t from-black via-black/95 to-transparent"
            style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));"
        >
            <div class="w-full max-w-md text-center">
                <p id="idvStepTitle" class="text-sm font-semibold text-white">Step 1 of 2: Capture ID front</p>
                <p id="idvSlotHint" class="text-xs text-amber-200/90 mt-1">Follow the steps in order.</p>
            </div>

            <div id="idvLiveControls" class="flex flex-col items-center gap-3 w-full max-w-md">
                <button
                    type="button"
                    id="idvCapture"
                    class="h-16 w-16 rounded-full border-4 border-white bg-white/20 shadow-lg ring-4 ring-white/30 disabled:opacity-40 disabled:pointer-events-none"
                    aria-label="Capture photo"
                ></button>
                <span class="text-xs text-slate-500">Tap to capture current step</span>
            </div>

            <div id="idvPreviewControls" class="hidden flex w-full max-w-md gap-3">
                <button type="button" id="idvRetakeBtn" class="btn-secondary flex-1 text-sm py-3">Retake</button>
                <button type="submit" id="idvSubmit" class="btn-primary flex-1 text-sm py-3" disabled>Submit verification</button>
            </div>
        </footer>
        </form>
</div>

<script>
(() => {
    const LS_AUTO = 'idv_auto_capture';
    const video = document.getElementById('idvVideo');
    const previewImg = document.getElementById('idvPreviewImg');
    const canvas = document.getElementById('idvCanvas');
    const captureBtn = document.getElementById('idvCapture');
    const retakeBtn = document.getElementById('idvRetakeBtn');
    const autoBtn = document.getElementById('idvAutoCaptureToggle');
    const submitBtn = document.getElementById('idvSubmit');
    const hint = document.getElementById('idvHint');
    const slotHint = document.getElementById('idvSlotHint');
    const stepTitle = document.getElementById('idvStepTitle');
    const permissionGate = document.getElementById('idvPermissionGate');
    const permissionText = document.getElementById('idvPermissionText');
    const enableBtn = document.getElementById('idvEnableBtn');
    const countdownWrap = document.getElementById('idvCountdown');
    const countdownNumber = document.getElementById('idvCountdownNumber');
    const idFrame = document.getElementById('idvGuideIdFrame');
    const faceFrame = document.getElementById('idvGuideFaceFrame');
    const guideLabel = document.getElementById('idvGuideLabel');
    const idZone = document.getElementById('idvIdZone');
    const faceZone = document.getElementById('idvFaceZone');
    const gridTint = document.getElementById('idvGridTint');
    const gridSvg = document.getElementById('idvGridSvg');

    const liveControls = document.getElementById('idvLiveControls');
    const previewControls = document.getElementById('idvPreviewControls');

    const inputs = {
        front: document.getElementById('id_front_base64'),
        selfie: document.getElementById('face_selfie_base64'),
    };

    let stream = null;
    let mode = 'live';
    let stepIndex = 0;
    let autoCapture = localStorage.getItem(LS_AUTO) === '1';
    let countdownTimer = null;
    let autoCaptureQueued = false;
    let alignmentGood = false;
    let alignCheckTimer = null;
    const detector = ('FaceDetector' in window) ? new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 }) : null;

    const steps = [
        { key: 'front', label: 'ID card front', title: 'Step 1 of 2: Capture ID front', hint: 'Hold the front side of your ID inside the rectangle frame.', guide: 'id' },
        { key: 'selfie', label: 'Selfie with ID', title: 'Step 2 of 2: Capture selfie with ID', hint: 'Keep your face in the circle and ID visible in frame.', guide: 'face' },
    ];

    function setHint(text) {
        if (hint) hint.textContent = text;
    }

    function setGuideState(isDetected, isGood, zoneEl) {
        if (!isDetected) {
            if (gridTint) gridTint.style.opacity = '0';
            if (gridSvg) gridSvg.style.color = 'rgba(255,255,255,0.52)';
            if (zoneEl) zoneEl.style.borderColor = 'rgba(255,255,255,0.86)';
            return;
        }
        const good = !!isGood;
        if (gridTint) {
            gridTint.style.background = good ? 'rgba(34, 197, 94, 0.22)' : 'rgba(239, 68, 68, 0.22)';
            gridTint.style.opacity = '1';
        }
        if (gridSvg) {
            gridSvg.style.color = good ? 'rgba(74, 222, 128, 0.92)' : 'rgba(248, 113, 113, 0.9)';
        }
        if (zoneEl) {
            zoneEl.style.borderColor = good ? 'rgba(34, 197, 94, 0.98)' : 'rgba(239, 68, 68, 0.95)';
        }
    }

    async function detectFaceState() {
        if (!video.videoWidth) return { detected: false, good: false };

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
            for (let y = 1; y < h - 1; y += 2) {
                for (let x = 1; x < w - 1; x += 2) {
                    const idx = (y * w + x) * 4;
                    const g = (image[idx] * 0.299) + (image[idx + 1] * 0.587) + (image[idx + 2] * 0.114);
                    const right = (image[idx + 4] * 0.299) + (image[idx + 5] * 0.587) + (image[idx + 6] * 0.114);
                    const downIdx = ((y + 1) * w + x) * 4;
                    const down = (image[downIdx] * 0.299) + (image[downIdx + 1] * 0.587) + (image[downIdx + 2] * 0.114);
                    const edge = Math.abs(g - right) + Math.abs(g - down);
                    const inCenter = x > 42 && x < 118 && y > 26 && y < 94;
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
            const detected = (centerAvg > 6 || outerAvg > 6) && lumAvg > 35;
            const edgeBalance = outerAvg > 0 ? (centerAvg / outerAvg) : 1;
            const good = detected && edgeBalance > 0.82 && edgeBalance < 1.18 && lumAvg > 55 && lumAvg < 210;
            return { detected, good };
        };

        if (!detector) return heuristic();
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
            const good = xRatio > 0.33 && xRatio < 0.67 && yRatio > 0.28 && yRatio < 0.63 && sizeRatio > 0.20 && sizeRatio < 0.58;
            return { detected: true, good };
        } catch (_e) {
            return heuristic();
        }
    }

    function detectIdStateByHeuristic() {
        if (!video.videoWidth) return { detected: false, good: false };
        const w = 160;
        const h = 120;
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(video, 0, 0, w, h);
        const image = ctx.getImageData(0, 0, w, h).data;
        let centerEnergy = 0;
        let outerEnergy = 0;
        let centerCount = 0;
        let outerCount = 0;
        for (let y = 1; y < h - 1; y += 2) {
            for (let x = 1; x < w - 1; x += 2) {
                const idx = (y * w + x) * 4;
                const g = (image[idx] * 0.299) + (image[idx + 1] * 0.587) + (image[idx + 2] * 0.114);
                const right = (image[idx + 4] * 0.299) + (image[idx + 5] * 0.587) + (image[idx + 6] * 0.114);
                const downIdx = ((y + 1) * w + x) * 4;
                const down = (image[downIdx] * 0.299) + (image[downIdx + 1] * 0.587) + (image[downIdx + 2] * 0.114);
                const edge = Math.abs(g - right) + Math.abs(g - down);
                const inCenter = x > 35 && x < 125 && y > 28 && y < 92;
                if (inCenter) {
                    centerEnergy += edge;
                    centerCount += 1;
                } else {
                    outerEnergy += edge;
                    outerCount += 1;
                }
            }
        }
        const centerAvg = centerCount ? centerEnergy / centerCount : 0;
        const outerAvg = outerCount ? outerEnergy / outerCount : 0;
        const detected = centerAvg > 6 || outerAvg > 6;
        const edgeBalance = outerAvg > 0 ? (centerAvg / outerAvg) : 1;
        const good = detected && edgeBalance > 0.84 && edgeBalance < 1.2;
        return { detected, good };
    }

    function startAlignmentLoop() {
        stopAlignmentLoop();
        alignCheckTimer = window.setInterval(async () => {
            if (mode !== 'live' || !stream) return;
            let state;
            if (steps[stepIndex]?.guide === 'id') {
                state = detectIdStateByHeuristic();
                setGuideState(state.detected, state.good, idZone);
            } else {
                state = await detectFaceState();
                setGuideState(state.detected, state.good, faceZone);
            }
            alignmentGood = !!(state && state.detected && state.good);
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

    function stopAlignmentLoop() {
        if (alignCheckTimer) {
            window.clearInterval(alignCheckTimer);
            alignCheckTimer = null;
        }
    }

    function showPermission(message) {
        if (permissionText && message) permissionText.textContent = message;
        permissionGate?.classList.remove('hidden');
    }

    function hidePermission() {
        permissionGate?.classList.add('hidden');
    }

    function syncAutoUi() {
        if (!autoBtn) return;
        autoBtn.textContent = autoCapture ? 'Auto: on' : 'Auto: off';
        autoBtn.setAttribute('aria-pressed', autoCapture ? 'true' : 'false');
    }

    function syncStepUi() {
        const step = steps[stepIndex];
        if (!step) return;
        if (stepTitle) stepTitle.textContent = step.title;
        if (slotHint) slotHint.textContent = 'Current: ' + step.label;
        if (guideLabel) guideLabel.textContent = step.guide === 'id'
            ? 'Align ID front in the card frame'
            : 'Center your face and keep ID visible';
        if (step.guide === 'id') {
            idFrame?.classList.remove('hidden');
            faceFrame?.classList.add('hidden');
            faceFrame?.classList.remove('flex');
            setGuideState(false, false, idZone);
        } else {
            idFrame?.classList.add('hidden');
            faceFrame?.classList.remove('hidden');
            faceFrame?.classList.add('flex');
            setGuideState(false, false, faceZone);
        }
        setHint(step.hint);
    }

    function refreshSubmit() {
        submitBtn.disabled = !(inputs.front.value && inputs.selfie.value);
    }

    function setMode(next) {
        mode = next;
        const live = next === 'live';
        if (live) {
            video.classList.remove('hidden');
            previewImg.classList.add('hidden');
            liveControls?.classList.remove('hidden');
            previewControls?.classList.add('hidden');
            startAlignmentLoop();
        } else {
            video.classList.add('hidden');
            previewImg.classList.remove('hidden');
            liveControls?.classList.add('hidden');
            previewControls?.classList.remove('hidden');
            stopAlignmentLoop();
        }
    }

    function clearCountdown() {
        if (countdownTimer) {
            window.clearInterval(countdownTimer);
            countdownTimer = null;
        }
        autoCaptureQueued = false;
        countdownWrap?.classList.add('hidden');
        countdownWrap?.classList.remove('flex');
    }

    function runAutoCountdown(onDone) {
        clearCountdown();
        autoCaptureQueued = true;
        let remaining = 3;
        if (countdownNumber) countdownNumber.textContent = String(remaining);
        countdownWrap?.classList.remove('hidden');
        countdownWrap?.classList.add('flex');
        countdownTimer = window.setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                    autoCaptureQueued = false;
                clearCountdown();
                onDone();
                return;
            }
            if (countdownNumber) countdownNumber.textContent = String(remaining);
        }, 1000);
    }

    function queueAutoCapture() {
        if (!autoCapture || autoCaptureQueued || !alignmentGood) return;
        runAutoCountdown(() => captureFrame(true));
    }

    async function startCamera() {
        hidePermission();
        stopCamera();
        clearCountdown();
        autoCaptureQueued = false;
        setMode('live');
        captureBtn.disabled = true;
        try {
            setHint('Requesting camera…');
            stream = await window.polarisRequestCameraOnly(video, {
                setHint: (t) => { if (hint) hint.textContent = t; },
            });
            syncStepUi();
            captureBtn.disabled = false;
        } catch (err) {
            console.error(err);
            showPermission('Unable to access camera. Please allow camera permission and try again.');
            setHint('Camera could not start.');
        }
    }

    function stopCamera() {
        clearCountdown();
        stopAlignmentLoop();
        autoCaptureQueued = false;
        captureBtn.disabled = true;
        if (stream) {
            stream.getTracks().forEach((t) => t.stop());
            stream = null;
            video.srcObject = null;
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
        setHint('Saved ' + step.label + '. You can now submit.');
    }

    captureBtn?.addEventListener('click', () => captureFrame(false));
    retakeBtn?.addEventListener('click', () => {
        previewImg.removeAttribute('src');
        stepIndex = 0;
        inputs.front.value = '';
        inputs.selfie.value = '';
        refreshSubmit();
        syncStepUi();
        startCamera();
    });
    autoBtn?.addEventListener('click', () => {
        autoCapture = !autoCapture;
        localStorage.setItem(LS_AUTO, autoCapture ? '1' : '0');
        syncAutoUi();
    });
    enableBtn?.addEventListener('click', startCamera);

    syncAutoUi();
    syncStepUi();
    setMode('live');
    showPermission('We need camera access for ID verification. Please allow camera to continue.');
    window.addEventListener('beforeunload', stopCamera);
})();
</script>
@endsection
