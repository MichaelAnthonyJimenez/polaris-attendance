@include('components.polaris-geo-camera-js')

@php
    $statusMessage = session('status');
    $sessionError = session('error');
@endphp

<div
    id="dvvShell"
    class="fixed inset-0 z-[100] flex flex-col bg-black text-white"
>
    <form id="facialVerificationForm" method="POST" action="{{ route('driver-verification.store') }}" class="flex flex-1 flex-col min-h-0">
        @csrf
        <input type="hidden" name="verification_method" value="facial">
        <input type="hidden" name="face_front_base64" id="dvv_face_front_base64" value="">
        <input type="hidden" name="face_left_base64" id="dvv_face_left_base64" value="">
        <input type="hidden" name="face_right_base64" id="dvv_face_right_base64" value="">

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
                <p class="text-sm font-semibold text-white truncate">Facial verification</p>
                @if ($sessionError)
                    <p class="text-xs font-medium text-red-300 truncate mt-0.5">{{ $sessionError }}</p>
                @endif
                @if ($statusMessage && !$sessionError)
                    <p class="text-xs font-medium text-emerald-300 truncate mt-0.5">{{ $statusMessage }}</p>
                @endif
                @if ($errors->any())
                    <p class="text-xs font-medium text-red-300 truncate mt-0.5">{{ $errors->first() }}</p>
                @endif
                <p id="dvvHint" class="text-xs text-slate-400 truncate mt-0.5">
                    Allow camera, align with the grid, then capture: front, left, right.
                </p>
            </div>
            <button
                type="button"
                id="dvvAutoCaptureToggle"
                class="shrink-0 flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold transition border-white/20 bg-white/10 text-white"
                aria-pressed="false"
                aria-label="Toggle auto capture"
                title="Auto capture when each step is ready"
            >
                <span class="relative flex h-2 w-2">
                    <span class="h-2 w-2 rounded-full bg-red-500" aria-hidden="true"></span>
                </span>
                <span id="dvvAutoCaptureLabel">Auto: off</span>
            </button>
        </header>

        <div class="flex-1 relative min-h-0 bg-black">
            <video
                id="dvvVideo"
                class="absolute inset-0 h-full w-full object-cover"
                autoplay
                playsinline
                muted
            ></video>
            <img
                id="dvvPreviewImg"
                src=""
                alt="Captured preview"
                class="absolute inset-0 hidden h-full w-full object-cover"
                width="1"
                height="1"
            />

            <div
                id="dvvPreviewNav"
                class="hidden absolute inset-0 z-[6] flex items-center justify-between px-1 sm:px-3 pointer-events-none"
                aria-hidden="true"
            >
                <button
                    type="button"
                    id="dvvPreviewPrev"
                    class="pointer-events-auto flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-full border border-white/25 bg-black/50 text-white shadow-lg backdrop-blur-sm transition hover:bg-black/65 disabled:opacity-25 disabled:pointer-events-none"
                    aria-label="Previous photo"
                >
                    <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button
                    type="button"
                    id="dvvPreviewNext"
                    class="pointer-events-auto flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-full border border-white/25 bg-black/50 text-white shadow-lg backdrop-blur-sm transition hover:bg-black/65 disabled:opacity-25 disabled:pointer-events-none"
                    aria-label="Next photo"
                >
                    <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            {{-- Guideline grid: rule-of-thirds + center cross --}}
            <div id="dvvFaceGuide" class="pointer-events-none absolute inset-0 z-[5] flex items-center justify-center p-4" style="z-index: 5;">
                <div
                    class="relative rounded-2xl border-2 overflow-hidden"
                    style="width: 88%; max-width: 32rem; aspect-ratio: 3 / 4; border-color: rgba(255, 255, 255, 0.72); box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.42);"
                >
                    <svg
                        id="dvvGridSvg"
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
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
                        <div id="dvvFaceZone" class="rounded-full border-[3px] border-dashed" style="width: 62%; max-width: 220px; aspect-ratio: 1 / 1; border-color: rgba(255,255,255,0.86); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.32) inset;"></div>
                    </div>
                    <div id="dvvGridTint" class="absolute inset-0 transition-opacity duration-200" style="opacity: 0; background: rgba(34, 197, 94, 0.22);"></div>
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

            <canvas id="dvvCanvas" class="hidden" width="2" height="2"></canvas>

            <div
                id="dvvPermissionGate"
                class="hidden absolute inset-0 z-10 flex flex-col items-center justify-center gap-4 bg-black/90 px-6 text-center"
            >
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 max-w-sm">
                    <p class="text-base font-semibold text-white mb-2">Camera access</p>
                    <p id="dvvPermissionText" class="text-sm text-slate-300 mb-5">
                        We need your camera for verification. When your browser asks, choose <strong class="text-white">Allow</strong> for the camera.
                    </p>
                    <button type="button" id="dvvRetryBtn" class="btn-primary w-full justify-center text-sm py-2.5">
                        Enable camera
                    </button>
                </div>
            </div>
            <div id="dvvCountdown" class="hidden absolute inset-0 z-[11] items-center justify-center pointer-events-none">
                <div id="dvvCountdownNumber" class="h-20 w-20 rounded-full border-2 border-white/60 bg-black/45 text-3xl font-bold flex items-center justify-center">3</div>
            </div>
        </div>

        <footer
            class="shrink-0 flex flex-col items-center gap-4 px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] bg-gradient-to-t from-black via-black/95 to-transparent"
            style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));"
        >
            <div class="w-full max-w-md text-center">
                <p class="text-xs text-slate-400 mb-1">Facial verification process</p>
                <p id="dvvStepTitle" class="text-sm font-semibold text-white">Step 1 of 3: Face the camera</p>
            </div>

            <div id="dvvStepThumbsRow" class="w-full max-w-md grid grid-cols-3 gap-2">
                <button type="button" id="dvvStepCellFront" class="dvv-step-thumb rounded-xl border border-white/15 bg-white/5 p-2 text-left transition ring-offset-2 ring-offset-black focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40" data-preview-index="0" tabindex="-1" aria-label="View front capture">
                    <p class="text-[10px] text-slate-300 mb-1 text-center">Step 1</p>
                    <img id="dvvStepPreviewFront" src="" alt="Front face preview" class="hidden h-14 w-full rounded-md object-cover" width="1" height="1" />
                    <div id="dvvStepPlaceholderFront" class="h-14 w-full rounded-md border border-dashed border-white/20 text-[10px] text-slate-400 flex items-center justify-center">Pending</div>
                </button>
                <button type="button" id="dvvStepCellLeft" class="dvv-step-thumb rounded-xl border border-white/15 bg-white/5 p-2 text-left transition ring-offset-2 ring-offset-black focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40" data-preview-index="1" tabindex="-1" aria-label="View left capture">
                    <p class="text-[10px] text-slate-300 mb-1 text-center">Step 2</p>
                    <img id="dvvStepPreviewLeft" src="" alt="Left face preview" class="hidden h-14 w-full rounded-md object-cover" width="1" height="1" />
                    <div id="dvvStepPlaceholderLeft" class="h-14 w-full rounded-md border border-dashed border-white/20 text-[10px] text-slate-400 flex items-center justify-center">Pending</div>
                </button>
                <button type="button" id="dvvStepCellRight" class="dvv-step-thumb rounded-xl border border-white/15 bg-white/5 p-2 text-left transition ring-offset-2 ring-offset-black focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40" data-preview-index="2" tabindex="-1" aria-label="View right capture">
                    <p class="text-[10px] text-slate-300 mb-1 text-center">Step 3</p>
                    <img id="dvvStepPreviewRight" src="" alt="Right face preview" class="hidden h-14 w-full rounded-md object-cover" width="1" height="1" />
                    <div id="dvvStepPlaceholderRight" class="h-14 w-full rounded-md border border-dashed border-white/20 text-[10px] text-slate-400 flex items-center justify-center">Pending</div>
                </button>
            </div>

            <div id="dvvLiveControls" class="flex flex-col items-center gap-3 w-full max-w-md">
                <button
                    type="button"
                    id="dvvCaptureBtn"
                    class="h-16 w-16 rounded-full border-4 border-white bg-white/20 shadow-lg ring-4 ring-white/30 disabled:opacity-40 disabled:pointer-events-none"
                    aria-label="Capture photo"
                ></button>
                <span class="text-xs text-slate-500">Tap to capture current step</span>
            </div>

            <div id="dvvPreviewControls" class="hidden flex w-full max-w-md gap-3">
                <button type="button" id="dvvRetakeBtn" class="btn-secondary flex-1 text-sm py-3">Retake</button>
                <button type="submit" id="dvvSubmitBtn" class="btn-primary flex-1 text-sm py-3">Submit verification</button>
            </div>
        </footer>
    </form>
</div>

<script>
    (() => {
        const LS_AUTO = 'dvv_auto_capture';
        const video = document.getElementById('dvvVideo');
        const canvas = document.getElementById('dvvCanvas');
        const previewImg = document.getElementById('dvvPreviewImg');
        const faceGuide = document.getElementById('dvvFaceGuide');
        const captureBtn = document.getElementById('dvvCaptureBtn');
        const submitBtn = document.getElementById('dvvSubmitBtn');
        const retakeBtn = document.getElementById('dvvRetakeBtn');
        const faceInput = document.getElementById('dvv_face_front_base64');
        const faceLeftInput = document.getElementById('dvv_face_left_base64');
        const faceRightInput = document.getElementById('dvv_face_right_base64');
        const hint = document.getElementById('dvvHint');
        const stepTitle = document.getElementById('dvvStepTitle');
        const permissionGate = document.getElementById('dvvPermissionGate');
        const permissionText = document.getElementById('dvvPermissionText');
        const retryBtn = document.getElementById('dvvRetryBtn');
        const countdownWrap = document.getElementById('dvvCountdown');
        const countdownNumber = document.getElementById('dvvCountdownNumber');
        const liveControls = document.getElementById('dvvLiveControls');
        const previewControls = document.getElementById('dvvPreviewControls');
        const form = document.getElementById('facialVerificationForm');
        const autoToggle = document.getElementById('dvvAutoCaptureToggle');
        const autoLabel = document.getElementById('dvvAutoCaptureLabel');
        const faceZone = document.getElementById('dvvFaceZone');
        const gridTint = document.getElementById('dvvGridTint');
        const gridSvg = document.getElementById('dvvGridSvg');
        const stepPreviewFront = document.getElementById('dvvStepPreviewFront');
        const stepPreviewLeft = document.getElementById('dvvStepPreviewLeft');
        const stepPreviewRight = document.getElementById('dvvStepPreviewRight');
        const stepPlaceholderFront = document.getElementById('dvvStepPlaceholderFront');
        const stepPlaceholderLeft = document.getElementById('dvvStepPlaceholderLeft');
        const stepPlaceholderRight = document.getElementById('dvvStepPlaceholderRight');
        const previewNav = document.getElementById('dvvPreviewNav');
        const previewPrev = document.getElementById('dvvPreviewPrev');
        const previewNext = document.getElementById('dvvPreviewNext');
        const stepThumbsRow = document.getElementById('dvvStepThumbsRow');
        const stepThumbCells = [
            document.getElementById('dvvStepCellFront'),
            document.getElementById('dvvStepCellLeft'),
            document.getElementById('dvvStepCellRight'),
        ];

        const REVIEW_LABELS = ['Front', 'Left', 'Right'];

        let stream = null;
        let mode = 'live';
        let submitting = false;
        let currentStep = 0;
        let autoCaptureQueued = false;
        let alignmentGood = false;
        let autoCapture = localStorage.getItem(LS_AUTO) === '1';
        let countdownTimer = null;
        let alignCheckTimer = null;
        let previewSlideIndex = 0;
        const detector = ('FaceDetector' in window) ? new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 }) : null;
        const steps = [
            { key: 'front', input: faceInput, title: 'Step 1 of 3: Face the camera', hint: 'Look straight at the camera and center your face in the guide.' },
            { key: 'left', input: faceLeftInput, title: 'Step 2 of 3: Turn left', hint: 'Slowly turn your head to the left and keep your face visible.' },
            { key: 'right', input: faceRightInput, title: 'Step 3 of 3: Turn right', hint: 'Now turn your head to the right and keep your face visible.' },
        ];
        const stepPreviewEls = {
            front: { image: stepPreviewFront, placeholder: stepPlaceholderFront },
            left: { image: stepPreviewLeft, placeholder: stepPlaceholderLeft },
            right: { image: stepPreviewRight, placeholder: stepPlaceholderRight },
        };

        function syncStepPreview(stepKey, dataUrl) {
            const preview = stepPreviewEls[stepKey];
            if (!preview) return;
            if (!dataUrl) {
                preview.image?.classList.add('hidden');
                preview.image?.removeAttribute('src');
                preview.placeholder?.classList.remove('hidden');
                return;
            }
            if (preview.image) {
                preview.image.src = dataUrl;
                preview.image.classList.remove('hidden');
            }
            preview.placeholder?.classList.add('hidden');
        }

        function resetAllStepPreviews() {
            syncStepPreview('front', '');
            syncStepPreview('left', '');
            syncStepPreview('right', '');
        }

        function clearPreviewThumbRings() {
            stepThumbCells.forEach((el) => {
                el?.classList.remove('ring-2', 'ring-white/90');
            });
        }

        function updatePreviewSlide(index) {
            const urls = [faceInput.value, faceLeftInput.value, faceRightInput.value];
            previewSlideIndex = Math.max(0, Math.min(2, index));
            const url = urls[previewSlideIndex];
            if (previewImg && url) previewImg.src = url;
            if (stepTitle) {
                stepTitle.textContent = 'Review: ' + REVIEW_LABELS[previewSlideIndex] + ' (' + (previewSlideIndex + 1) + ' of 3)';
            }
            if (previewPrev) previewPrev.disabled = previewSlideIndex === 0;
            if (previewNext) previewNext.disabled = previewSlideIndex === 2;
            stepThumbCells.forEach((el, i) => {
                if (!el) return;
                const on = i === previewSlideIndex;
                el.classList.toggle('ring-2', on);
                el.classList.toggle('ring-white/90', on);
            });
        }

        function updateStepUi() {
            const step = steps[currentStep];
            if (stepTitle) stepTitle.textContent = step.title;
            setHint(step.hint);
        }

        function syncAutoUi() {
            if (autoLabel) autoLabel.textContent = autoCapture ? 'Auto: on' : 'Auto: off';
            if (autoToggle) {
                autoToggle.setAttribute('aria-pressed', autoCapture ? 'true' : 'false');
                autoToggle.classList.toggle('bg-blue-600/50', autoCapture);
                autoToggle.classList.toggle('border-blue-400/50', autoCapture);
            }
        }

        function setHint(text) {
            if (hint) hint.textContent = text;
        }

        function setGuideState(isDetected, isGood) {
            if (!isDetected) {
                if (gridTint) gridTint.style.opacity = '0';
                if (gridSvg) gridSvg.style.color = 'rgba(255,255,255,0.52)';
                if (faceZone) faceZone.style.borderColor = 'rgba(255,255,255,0.86)';
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
            if (faceZone) {
                faceZone.style.borderColor = good ? 'rgba(34, 197, 94, 0.98)' : 'rgba(239, 68, 68, 0.95)';
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
                const good = xRatio > 0.33 && xRatio < 0.67 && yRatio > 0.28 && yRatio < 0.63 && sizeRatio > 0.22 && sizeRatio < 0.56;
                return { detected: true, good };
            } catch (_e) {
                return heuristic();
            }
        }

        function startAlignmentLoop() {
            stopAlignmentLoop();
            setGuideState(false, false);
            alignCheckTimer = window.setInterval(async () => {
                if (mode !== 'live' || !stream) return;
                const state = await detectFaceState();
                setGuideState(state.detected, state.good);
                alignmentGood = !!(state.detected && state.good);
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

        function clearCountdown() {
            if (countdownTimer) {
                window.clearInterval(countdownTimer);
                countdownTimer = null;
            }
            autoCaptureQueued = false;
            countdownWrap?.classList.add('hidden');
            countdownWrap?.classList.remove('flex');
        }

        function queueAutoCapture() {
            if (!autoCapture || autoCaptureQueued || !alignmentGood) return;
            runAutoCountdown(() => captureFrame({ auto: true }));
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
                    clearCountdown();
                    onDone();
                    return;
                }
                if (countdownNumber) countdownNumber.textContent = String(remaining);
            }, 1000);
        }

        function setMode(next) {
            mode = next;
            const live = next === 'live';
            if (live) {
                video.classList.remove('hidden');
                previewImg.classList.add('hidden');
                if (faceGuide) faceGuide.classList.remove('hidden');
                liveControls.classList.remove('hidden');
                previewControls.classList.add('hidden');
                startAlignmentLoop();
            } else {
                video.classList.add('hidden');
                previewImg.classList.remove('hidden');
                if (faceGuide) faceGuide.classList.add('hidden');
                liveControls.classList.add('hidden');
                previewControls.classList.remove('hidden');
                stopAlignmentLoop();
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach((t) => t.stop());
                stream = null;
            }
            clearCountdown();
            stopAlignmentLoop();
            if (video) video.srcObject = null;
        }

        function showPermissionGate(message) {
            if (permissionText && message) permissionText.textContent = message;
            permissionGate?.classList.remove('hidden');
        }

        function hidePermissionGate() {
            permissionGate?.classList.add('hidden');
        }

        async function startCamera() {
            hidePermissionGate();
            stopCamera();
            setMode('live');
            try {
                setHint('Opening camera…');
                stream = await window.polarisRequestCameraOnly(video, { setHint });
                updateStepUi();
                captureBtn.disabled = false;
            } catch (err) {
                console.error(err);
                captureBtn.disabled = true;
                showPermissionGate(
                    'We could not use the camera. Allow camera access in your browser settings, or tap below to try again.'
                );
                setHint('Camera not available.');
            }
        }

        async function retake(allSteps = false) {
            previewSlideIndex = 0;
            if (allSteps) {
                currentStep = 0;
                faceInput.value = '';
                faceLeftInput.value = '';
                faceRightInput.value = '';
                resetAllStepPreviews();
            } else if (steps[currentStep]) {
                steps[currentStep].input.value = '';
                syncStepPreview(steps[currentStep].key, '');
            }
            previewImg.removeAttribute('src');
            submitting = false;
            submitBtn.disabled = false;
            await startCamera();
        }

        captureBtn?.addEventListener('click', () => captureFrame({ auto: false }));
        retakeBtn?.addEventListener('click', () => retake(true));
        retryBtn?.addEventListener('click', () => startCamera());
        previewPrev?.addEventListener('click', () => {
            if (mode === 'preview') updatePreviewSlide(previewSlideIndex - 1);
        });
        previewNext?.addEventListener('click', () => {
            if (mode === 'preview') updatePreviewSlide(previewSlideIndex + 1);
        });
        stepThumbCells.forEach((cell, i) => {
            cell?.addEventListener('click', () => {
                if (mode === 'preview') updatePreviewSlide(i);
            });
        });
        window.addEventListener('keydown', (e) => {
            if (mode !== 'preview') return;
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                updatePreviewSlide(previewSlideIndex - 1);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                updatePreviewSlide(previewSlideIndex + 1);
            }
        });
        autoToggle?.addEventListener('click', () => {
            autoCapture = !autoCapture;
            localStorage.setItem(LS_AUTO, autoCapture ? '1' : '0');
            syncAutoUi();
        });
        form?.addEventListener('submit', () => {
            submitting = true;
            submitBtn.disabled = true;
        });

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

            const step = steps[currentStep];
            if (!step) return;
            step.input.value = dataUrl;
            syncStepPreview(step.key, dataUrl);

            if (currentStep < steps.length - 1) {
                currentStep += 1;
                updateStepUi();
                setHint(steps[currentStep].hint + ' Capture when ready.');
                return;
            }

            previewImg.src = dataUrl;
            setMode('preview');
            setHint('All steps captured. Review your final frame, then submit or retake all steps.');
        }

        captureBtn.disabled = true;
        syncAutoUi();
        updateStepUi();
        resetAllStepPreviews();
        if (stepThumbsRow) stepThumbsRow.classList.add('pointer-events-none');
        showPermissionGate(
            'We need your camera for facial verification. Tap below and allow camera access when prompted.'
        );
        window.addEventListener('beforeunload', stopCamera);
    })();
</script>
