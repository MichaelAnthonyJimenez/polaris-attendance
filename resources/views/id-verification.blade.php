@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

<div
    id="idvShell"
    class="fixed inset-0 z-[2147483647] flex flex-col bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 text-white"
    style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100vw; height: 100vh;"
>
    <form method="POST" action="{{ route('driver-verification.store') }}" id="idVerificationForm" class="flex flex-1 flex-col min-h-0">
        @csrf
        <input type="hidden" name="verification_method" value="id_only">
        <input type="hidden" name="proof_mode" id="idv_proof_mode" value="">
        <input type="hidden" name="id_front_base64" id="id_front_base64">
        <input type="hidden" name="id_back_base64" id="id_back_base64">
        <input type="hidden" name="face_selfie_base64" id="face_selfie_base64">
        <input type="hidden" name="latitude" id="idv_latitude">
        <input type="hidden" name="longitude" id="idv_longitude">
        <input type="hidden" name="geo_accuracy" id="idv_geo_accuracy">

        <header
            class="idv-header-when-not-upload flex shrink-0 items-center gap-2 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 bg-gradient-to-b from-slate-900/90 to-transparent border-b border-white/5"
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
                <p id="idvHint" class="text-xs text-slate-400 truncate mt-0.5">Select a mode to continue.</p>
            </div>
        </header>

        {{-- Upload-only: simple header (no camera) --}}
        <header
            id="idvHeaderUpload"
            class="hidden flex shrink-0 items-center gap-2 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 bg-slate-900/50 border-b border-white/5"
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
                <p class="text-sm font-semibold text-white truncate">Upload ID</p>
                <p class="text-xs text-slate-400 truncate mt-0.5">Choose your ID type and images.</p>
            </div>
        </header>

        {{-- Selfie: camera + guides --}}
        <div id="idvMainCameraBlock" class="hidden flex-1 relative min-h-0 bg-black">
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
                <div class="relative rounded-2xl border-2 overflow-hidden" style="width: 90%; max-width: 36rem; aspect-ratio: 3 / 4; border-color: rgba(255, 255, 255, 0.85); box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);">
                    <svg
                        id="idvGridSvg"
                        class="absolute inset-0 h-full w-full transition-all duration-300"
                        viewBox="0 0 300 400"
                        preserveAspectRatio="none"
                        style="color: rgba(255,255,255,0.6);"
                        aria-hidden="true"
                    >
                        <!-- Enhanced grid with corner markers for better ID positioning -->
                        <line x1="100" y1="0" x2="100" y2="400" stroke="currentColor" stroke-width="1.8" />
                        <line x1="200" y1="0" x2="200" y2="400" stroke="currentColor" stroke-width="1.8" />
                        <line x1="0" y1="133.3" x2="300" y2="133.3" stroke="currentColor" stroke-width="1.8" />
                        <line x1="0" y1="266.6" x2="300" y2="266.6" stroke="currentColor" stroke-width="1.8" />
                        <!-- Corner markers for ID frame alignment -->
                        <path d="M 40 60 L 40 80 L 60 80" stroke="currentColor" stroke-width="2.5" fill="none" />
                        <path d="M 260 60 L 240 60 L 240 80" stroke="currentColor" stroke-width="2.5" fill="none" />
                        <path d="M 40 340 L 60 340 L 60 320" stroke="currentColor" stroke-width="2.5" fill="none" />
                        <path d="M 260 340 L 260 320 L 240 320" stroke="currentColor" stroke-width="2.5" fill="none" />
                    </svg>
                    <div id="idvGuideIdFrame" class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                        <div id="idvIdZone" class="rounded-xl border-[3px] border-dashed transition-all duration-300" style="width: 82%; max-width: 360px; aspect-ratio: 1.6 / 1; border-color: rgba(255,255,255,0.9); box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.4) inset, 0 0 20px rgba(255,255,255,0.1);">
                            <!-- Inner guide lines for ID card edges -->
                            <div class="absolute inset-2 border border-white/20 rounded-lg pointer-events-none"></div>
                            <!-- Center crosshair for precise alignment -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-8 h-8">
                                <div class="absolute top-1/2 left-0 w-full h-0.5 bg-white/30 -translate-y-1/2"></div>
                                <div class="absolute top-0 left-1/2 w-0.5 h-full bg-white/30 -translate-x-1/2"></div>
                            </div>
                        </div>
                    </div>
                    <div id="idvGuideFaceFrame" class="absolute inset-0 hidden items-center justify-center" aria-hidden="true">
                        <div id="idvFaceZone" class="rounded-full border-[3px] border-dashed transition-all duration-300" style="width: 62%; max-width: 220px; aspect-ratio: 1 / 1; border-color: rgba(255,255,255,0.9); box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.4) inset, 0 0 20px rgba(255,255,255,0.1);">
                            <!-- Face guide oval -->
                            <div class="absolute inset-3 border-2 border-white/20 rounded-full pointer-events-none"></div>
                        </div>
                    </div>
                    <div id="idvGridTint" class="absolute inset-0 transition-all duration-300" style="opacity: 0; background: rgba(34, 197, 94, 0.22);"></div>
                    <div class="absolute top-2 right-2 rounded-lg bg-black/60 backdrop-blur-sm px-3 py-1.5 text-[10px] sm:text-xs font-medium text-white/90 border border-white/10">
                        <span class="inline-flex items-center gap-1.5 mr-3">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span> Proper
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span> Adjust
                        </span>
                    </div>
                    <div class="absolute bottom-2 left-0 right-0 text-center px-2">
                        <span id="idvGuideLabel" class="text-[10px] sm:text-xs font-semibold text-white/95 drop-shadow-lg transition-colors duration-300">Align ID front in the card frame</span>
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
                <div class="rounded-2xl border border-white/10 bg-slate-900/60 backdrop-blur-md p-6 max-w-sm">
                    <p class="text-base font-semibold text-white mb-2">Camera access</p>
                    <p id="idvPermissionText" class="text-sm text-slate-300 mb-5">
                        We need camera access for ID verification. Please allow camera when prompted.
                    </p>
                    <button type="button" id="idvEnableBtn" class="btn-primary w-full justify-center text-sm py-2.5">Continue</button>
                </div>
            </div>
        </div>

        {{-- Upload: no camera --}}
        <div id="idvUploadOnlyBlock" class="hidden flex-1 min-h-0 flex flex-col overflow-y-auto px-4 py-6" style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));">
            <div class="w-full max-w-md mx-auto glass p-5 sm:p-6 rounded-2xl border border-white/10">
                <label class="block text-xs text-slate-300 mb-1.5">ID type (Philippines)</label>
                <select id="idv_id_type" name="id_type" class="form-select text-sm py-2.5 mb-4 w-full">
                    <option value="philsys_national_id">PhilSys National ID</option>
                    <option value="drivers_license">Driver's License</option>
                    <option value="passport">Passport</option>
                    <option value="umid">UMID</option>
                    <option value="prc_id">PRC ID</option>
                    <option value="postal_id">Postal ID</option>
                    <option value="voters_id">Voter's ID</option>
                    <option value="philhealth_id">PhilHealth ID</option>
                    <option value="sss_id">SSS ID</option>
                    <option value="pagibig_loyalty_card">Pag-IBIG Loyalty Card</option>
                    <option value="senior_citizen_id">Senior Citizen ID</option>
                    <option value="ofw_id">OFW ID</option>
                    <option value="barangay_id">Barangay ID</option>
                    <option value="other">Other</option>
                </select>
                <label class="block text-xs text-slate-300 mb-1.5">ID front (required)</label>
                <input type="file" id="idv_upload_front" name="id_front_file" accept="image/*" class="form-input text-sm mb-4 w-full">
                <label class="block text-xs text-slate-300 mb-1.5">ID back (optional)</label>
                <input type="file" id="idv_upload_back" name="id_back_file" accept="image/*" class="form-input text-sm mb-6 w-full">
                <button type="submit" id="idvUploadSubmit" class="btn-primary w-full py-3 text-sm" disabled>Submit verification</button>
            </div>
        </div>

        {{-- Selfie mode footer: steps + capture row (reverse beside circle) --}}
        <footer
            id="idvSelfieFooter"
            class="hidden shrink-0 flex flex-col items-center gap-4 px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] bg-gradient-to-t from-black via-black/95 to-transparent"
            style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));"
        >
            <div class="w-full max-w-md text-center">
                <p id="idvStepTitle" class="text-sm font-semibold text-white">Step 1 of 2: Capture ID front</p>
                <p id="idvSlotHint" class="text-xs text-amber-200/90 mt-1">Follow the steps in order.</p>
            </div>

            <div id="idvLiveControls" class="flex flex-row items-center justify-center gap-5 w-full max-w-md">
                <button
                    type="button"
                    id="idvCameraToggle"
                    class="btn-secondary h-12 w-12 shrink-0 rounded-full p-0 flex items-center justify-center"
                    aria-pressed="true"
                    aria-label="Reverse camera"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7h3a2 2 0 012 2v3m-2-5l-3 3m0 0a7 7 0 10.88 9.88M8 17H5a2 2 0 01-2-2v-3m2 5l3-3m0 0a7 7 0 10-.88-9.88"></path>
                    </svg>
                </button>
                <button
                    type="button"
                    id="idvCapture"
                    class="h-16 w-16 rounded-full border-4 border-white bg-white/20 shadow-lg ring-4 ring-white/30 disabled:opacity-40 disabled:pointer-events-none shrink-0"
                    aria-label="Capture photo"
                ></button>
                <button
                    type="button"
                    id="idvAutoCaptureToggle"
                    class="btn-secondary text-[10px] sm:text-xs px-2.5 py-2 min-w-[4.5rem] h-12 rounded-xl"
                    aria-pressed="false"
                >Auto: off</button>
            </div>
            <span class="text-xs text-slate-500 -mt-1">Tap the circle to capture the current step</span>

            <div id="idvPreviewControls" class="hidden flex w-full max-w-md gap-3">
                <button type="button" id="idvRetakeBtn" class="btn-secondary flex-1 text-sm py-3">Retake</button>
                <button type="submit" id="idvSubmit" class="btn-primary flex-1 text-sm py-3" disabled>Submit verification</button>
            </div>
        </footer>

        {{-- Mode gate: system-style (glass) --}}
        <div
            id="idvModeGate"
            class="absolute inset-0 z-[260] flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md"
        >
            <div class="w-full max-w-sm glass p-6 sm:p-8 rounded-2xl border border-white/10 shadow-2xl shadow-slate-950/50 text-center">
                <h2 class="text-lg sm:text-xl font-semibold text-white">Select verification mode</h2>
                <p class="mt-2 text-sm text-slate-300">Choose how you want to submit your ID.</p>
                <div class="mt-6 grid grid-cols-1 gap-3">
                    <button type="button" id="idvGateSelfie" class="btn-primary w-full justify-center py-2.5 text-sm">Selfie with ID</button>
                    <button type="button" id="idvGateUpload" class="btn-secondary w-full justify-center py-2.5 text-sm">Upload ID files</button>
                </div>
            </div>
        </div>
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
    const cameraToggleBtn = document.getElementById('idvCameraToggle');
    const submitBtn = document.getElementById('idvSubmit');
    const uploadSubmitBtn = document.getElementById('idvUploadSubmit');
    const gateSelfieBtn = document.getElementById('idvGateSelfie');
    const gateUploadBtn = document.getElementById('idvGateUpload');
    const modeGate = document.getElementById('idvModeGate');
    const proofModeInput = document.getElementById('idv_proof_mode');
    const idTypeSelect = document.getElementById('idv_id_type');
    const uploadFrontInput = document.getElementById('idv_upload_front');
    const mainCameraBlock = document.getElementById('idvMainCameraBlock');
    const uploadOnlyBlock = document.getElementById('idvUploadOnlyBlock');
    const selfieFooter = document.getElementById('idvSelfieFooter');
    const headerNotUpload = document.querySelectorAll('.idv-header-when-not-upload');
    const headerUpload = document.getElementById('idvHeaderUpload');
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
    let proofMode = '';
    let cameraFacingMode = 'environment';
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
            if (zoneEl) {
                zoneEl.style.borderColor = 'rgba(255,255,255,0.86)';
                zoneEl.style.boxShadow = '0 0 0 1px rgba(0, 0, 0, 0.32) inset';
                zoneEl.style.borderWidth = '3px';
            }
            // Reset hint to default positioning message
            if (steps[stepIndex]?.guide === 'id') {
                setHint('Position your ID card within the frame. Ensure all edges are visible.');
            }
            return;
        }

        const good = !!isGood;
        const guideLabel = document.getElementById('idvGuideLabel');

        if (gridTint) {
            if (good) {
                gridTint.style.background = 'rgba(34, 197, 94, 0.25)';
                gridTint.style.opacity = '1';
            } else {
                gridTint.style.background = 'rgba(239, 68, 68, 0.20)';
                gridTint.style.opacity = '1';
            }
        }

        if (gridSvg) {
            if (good) {
                gridSvg.style.color = 'rgba(74, 222, 128, 1)';
                gridSvg.style.filter = 'drop-shadow(0 0 8px rgba(74, 222, 128, 0.6))';
                gridSvg.style.strokeWidth = '2';
            } else {
                gridSvg.style.color = 'rgba(248, 113, 113, 0.9)';
                gridSvg.style.filter = 'none';
                gridSvg.style.strokeWidth = '1.6';
            }
        }

        if (zoneEl) {
            if (good) {
                zoneEl.style.borderColor = 'rgba(34, 197, 92, 1)';
                zoneEl.style.borderWidth = '4px';
                zoneEl.style.boxShadow = '0 0 0 3px rgba(34, 197, 92, 0.3), 0 0 25px rgba(34, 197, 92, 0.5)';
                zoneEl.style.transition = 'all 0.3s ease';
                zoneEl.style.transform = 'scale(1.02)';

                // Update hint for good positioning
                if (steps[stepIndex]?.guide === 'id') {
                    setHint('Perfect! ID is properly positioned. Hold steady or capture now.');
                }
            } else {
                zoneEl.style.borderColor = 'rgba(239, 68, 68, 0.95)';
                zoneEl.style.borderWidth = '3px';
                zoneEl.style.boxShadow = '0 0 0 2px rgba(239, 68, 68, 0.4), 0 0 15px rgba(239, 68, 68, 0.3)';
                zoneEl.style.transition = 'all 0.2s ease';
                zoneEl.style.transform = 'scale(1)';

                // Update hint for poor positioning
                if (steps[stepIndex]?.guide === 'id') {
                    setHint('Adjust ID position. Center the card and ensure edges are clearly visible.');
                }
            }
        }

        // Update guide label with specific feedback
        if (guideLabel) {
            if (good) {
                guideLabel.textContent = '✓ ID properly positioned';
                guideLabel.style.color = 'rgba(74, 222, 128, 1)';
            } else {
                guideLabel.textContent = 'Adjust positioning';
                guideLabel.style.color = 'rgba(248, 113, 113, 1)';
            }
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

            // Enhanced face detection heuristic for selfie with ID
            let skinTonePixels = 0;
            let totalPixels = 0;
            let centerActivity = 0;
            let edgeActivity = 0;
            let facialFeatures = 0;

            // Define optimal face region for selfie with ID (slightly larger for better detection)
            const faceLeft = Math.floor(w * 0.25);
            const faceRight = Math.floor(w * 0.75);
            const faceTop = Math.floor(h * 0.15);
            const faceBottom = Math.floor(h * 0.7);

            // Define eye region (upper third of face area)
            const eyeTop = faceTop + Math.floor((faceBottom - faceTop) * 0.2);
            const eyeBottom = faceTop + Math.floor((faceBottom - faceTop) * 0.4);

            for (let y = 0; y < h; y += 2) {
                for (let x = 0; x < w; x += 2) {
                    const idx = (y * w + x) * 4;
                    const r = image[idx];
                    const g = image[idx + 1];
                    const b = image[idx + 2];

                    // Enhanced skin tone detection for various skin tones
                    const isSkinTone = (
                        (r > 95 && g > 40 && b > 20 && r > g && r > b && r - g > 15 && r - b > 15) || // Light skin
                        (r > 80 && g > 30 && b > 20 && r > g && r > b && r - g > 10 && r - b > 10) || // Medium skin
                        (r > 70 && g > 25 && b > 15 && r > g && r > b && r - g > 8 && r - b > 8) // Dark skin
                    );

                    if (isSkinTone) {
                        skinTonePixels++;
                    }

                    totalPixels++;

                    // Check for facial features in center region
                    if (x >= faceLeft && x <= faceRight && y >= faceTop && y <= faceBottom) {
                        const brightness = (r + g + b) / 3;

                        // Eye region detection (darker areas)
                        if (y >= eyeTop && y <= eyeBottom && brightness < 120 && brightness > 40) {
                            facialFeatures++;
                        }

                        // General face area detection (medium brightness)
                        if (brightness > 80 && brightness < 180) {
                            centerActivity++;
                        }
                    } else {
                        // Background should be different from face
                        const brightness = (r + g + b) / 3;
                        if (brightness > 60) {
                            edgeActivity++;
                        }
                    }
                }
            }

            const skinRatio = totalPixels > 0 ? skinTonePixels / totalPixels : 0;
            const centerRatio = centerActivity > 0 ? centerActivity / ((faceRight - faceLeft) * (faceBottom - faceTop) / 4) : 0;
            const edgeRatio = edgeActivity > 0 ? edgeActivity / (totalPixels / 4 - centerActivity) : 0;
            const featureRatio = facialFeatures > 0 ? facialFeatures / ((faceRight - faceLeft) * (eyeBottom - eyeTop) / 4) : 0;

            // Enhanced detection criteria for selfie with ID
            const hasSkinTones = skinRatio > 0.12; // Slightly more lenient for various skin tones
            const hasFaceCenter = centerRatio > 0.25;
            const hasFacialFeatures = featureRatio > 0.15;
            const goodFacePosition = centerRatio > edgeRatio * 0.7;

            const detected = hasSkinTones && hasFaceCenter;
            const good = detected && hasFacialFeatures && goodFacePosition && skinRatio > 0.15 && centerRatio > 0.35;

            return { detected, good };
        };

        if (detector) {
            try {
                const faces = await detector.detectFaces(video);
                if (faces.length > 0) {
                    const face = faces[0].boundingBox;
                    const layout = { xRatio: face.x / video.videoWidth, yRatio: face.y / video.videoHeight, sizeRatio: (face.width * face.height) / (video.videoWidth * video.videoHeight) };
                    // More lenient positioning for selfie with ID (allow slightly larger faces)
                    const good = layout.xRatio > 0.25 && layout.xRatio < 0.75 && layout.yRatio > 0.20 && layout.yRatio < 0.70 && layout.sizeRatio > 0.15 && layout.sizeRatio < 0.55;
                    return { detected: true, good };
                }
            } catch (_e) {
                return heuristic();
            }
        }
        return heuristic();
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

        // Enhanced ID detection with more precise center region
        // ID frame should be in the center with proper aspect ratio
        const idFrameLeft = 32;  // ~20% from left
        const idFrameRight = 128; // ~80% from left
        const idFrameTop = 24;   // ~20% from top
        const idFrameBottom = 96; // ~80% from top

        let centerEnergy = 0;
        let outerEnergy = 0;
        let centerCount = 0;
        let outerCount = 0;
        let brightnessVariance = 0;
        let edgeConsistency = 0;

        for (let y = 1; y < h - 1; y += 2) {
            for (let x = 1; x < w - 1; x += 2) {
                const idx = (y * w + x) * 4;
                const r = image[idx];
                const g = image[idx + 1];
                const b = image[idx + 2];
                const gray = (r * 0.299) + (g * 0.587) + (b * 0.114);

                const rightIdx = (y * w + x + 2) * 4;
                const rightGray = (image[rightIdx] * 0.299) + (image[rightIdx + 1] * 0.587) + (image[rightIdx + 2] * 0.114);
                const downIdx = ((y + 2) * w + x) * 4;
                const downGray = (image[downIdx] * 0.299) + (image[downIdx + 1] * 0.587) + (image[downIdx + 2] * 0.114);

                const edgeStrength = Math.abs(gray - rightGray) + Math.abs(gray - downGray);

                // Check if pixel is in the ID frame region (more precise)
                const inIdFrame = x >= idFrameLeft && x <= idFrameRight && y >= idFrameTop && y <= idFrameBottom;

                // Additional check for proper ID card shape (rectangular with edges)
                const inEdgeZone = (x >= idFrameLeft - 5 && x <= idFrameLeft + 5) ||
                                  (x >= idFrameRight - 5 && x <= idFrameRight + 5) ||
                                  (y >= idFrameTop - 5 && y <= idFrameTop + 5) ||
                                  (y >= idFrameBottom - 5 && y <= idFrameBottom + 5);

                if (inIdFrame) {
                    centerEnergy += edgeStrength;
                    centerCount += 1;
                    // Check for consistent brightness (typical of ID cards)
                    if (x > idFrameLeft + 10 && x < idFrameRight - 10 && y > idFrameTop + 10 && y < idFrameBottom - 10) {
                        brightnessVariance += Math.abs(gray - 128); // Expect mid-range brightness
                    }
                } else {
                    outerEnergy += edgeStrength;
                    outerCount += 1;
                    // Check for strong edges at frame boundaries
                    if (inEdgeZone && edgeStrength > 15) {
                        edgeConsistency += 1;
                    }
                }
            }
        }

        const centerAvg = centerCount ? centerEnergy / centerCount : 0;
        const outerAvg = outerCount ? outerEnergy / outerCount : 0;
        const brightnessScore = centerCount ? brightnessVariance / centerCount : 0;

        // Enhanced detection criteria
        const hasEdges = centerAvg > 8 || outerAvg > 8;
        const edgeBalance = outerAvg > 0 ? (centerAvg / outerAvg) : 1;
        const properEdgeRatio = edgeBalance > 0.75 && edgeBalance < 1.4;
        const goodBrightness = brightnessScore < 50; // Not too dark or too bright
        const hasFrameEdges = edgeConsistency > 3; // Strong edges at expected frame boundaries

        const detected = hasEdges && (centerAvg > 10 || outerAvg > 10);
        const good = detected && properEdgeRatio && goodBrightness && hasFrameEdges;

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

    function syncCameraUi() {
        if (!cameraToggleBtn) return;
        const isRear = cameraFacingMode === 'environment';
        cameraToggleBtn.setAttribute('aria-pressed', isRear ? 'true' : 'false');
    }

    function syncStepUi() {
        if (proofMode === 'upload_file') {
            return;
        }
        if (!proofMode) {
            if (stepTitle) stepTitle.textContent = 'Select verification mode';
            if (slotHint) slotHint.textContent = 'Choose a mode on the previous screen.';
            if (guideLabel) guideLabel.textContent = 'Select verification mode to continue';
            idFrame?.classList.remove('hidden');
            faceFrame?.classList.add('hidden');
            faceFrame?.classList.remove('flex');
            setGuideState(false, false, idZone);
            setHint('Select a verification mode first.');
            return;
        }
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
        if (proofMode === 'upload_file') {
            if (uploadSubmitBtn) {
                uploadSubmitBtn.disabled = !(uploadFrontInput && uploadFrontInput.files && uploadFrontInput.files.length > 0);
            }
            return;
        }
        if (submitBtn) {
            submitBtn.disabled = !(inputs.front.value && inputs.selfie.value);
        }
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
                facingMode: cameraFacingMode,
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

    function applyLayoutForMode() {
        if (proofMode === 'upload_file') {
            if (headerNotUpload) headerNotUpload.forEach((el) => el.classList.add('hidden'));
            headerUpload?.classList.remove('hidden');
            mainCameraBlock?.classList.add('hidden');
            uploadOnlyBlock?.classList.remove('hidden');
            selfieFooter?.classList.add('hidden');
            return;
        }
        headerUpload?.classList.add('hidden');
        if (headerNotUpload) headerNotUpload.forEach((el) => el.classList.remove('hidden'));
        uploadOnlyBlock?.classList.add('hidden');
        if (proofMode === 'selfie_with_id') {
            mainCameraBlock?.classList.remove('hidden');
            selfieFooter?.classList.remove('hidden');
        } else {
            mainCameraBlock?.classList.add('hidden');
            selfieFooter?.classList.add('hidden');
        }
    }

    function setProofMode(nextMode) {
        proofMode = nextMode;
        if (proofModeInput) proofModeInput.value = proofMode;
        if (modeGate) modeGate.classList.add('hidden');
        if (idTypeSelect) {
            if (proofMode === 'upload_file') {
                idTypeSelect.value = idTypeSelect.value || 'philsys_national_id';
            }
        }
        clearCountdown();
        stopAlignmentLoop();
        stepIndex = 0;
        inputs.front.value = '';
        inputs.selfie.value = '';
        previewImg.removeAttribute('src');
        applyLayoutForMode();
        syncStepUi();
        refreshSubmit();

        if (proofMode === 'upload_file') {
            hidePermission();
            stopCamera();
        } else if (proofMode === 'selfie_with_id') {
            document.getElementById('idvGuide')?.classList.remove('hidden');
            setMode('live');
            showPermission('We need camera access for ID verification. Please allow camera to continue.');
        }
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
    cameraToggleBtn?.addEventListener('click', async () => {
        cameraFacingMode = cameraFacingMode === 'environment' ? 'user' : 'environment';
        syncCameraUi();
        if (mode === 'live') {
            await startCamera();
        }
    });

    gateSelfieBtn?.addEventListener('click', () => {
        setProofMode('selfie_with_id');
    });
    gateUploadBtn?.addEventListener('click', () => {
        setProofMode('upload_file');
    });
    uploadFrontInput?.addEventListener('change', refreshSubmit);
    enableBtn?.addEventListener('click', () => {
        if (proofMode === 'upload_file') return;
        startCamera();
    });

    syncAutoUi();
    syncCameraUi();
    applyLayoutForMode();
    if (idTypeSelect) {
        idTypeSelect.value = 'philsys_national_id';
    }
    syncStepUi();
    if (mainCameraBlock) {
        mainCameraBlock.classList.add('hidden');
    }
    selfieFooter?.classList.add('hidden');
    setMode('preview');
    previewImg.classList.add('hidden');
    video.classList.add('hidden');
    hidePermission();
    window.addEventListener('beforeunload', stopCamera);
})();
</script>
@endsection
