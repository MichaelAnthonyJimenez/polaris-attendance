@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

<div
    id="idvShell"
    class="fixed inset-0 z-[2147483647] flex flex-col bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 text-white"
    style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100vw; height: 100dvh;"
>
    <form method="POST" action="{{ route('driver-verification.store') }}" id="idVerificationForm" enctype="multipart/form-data" class="relative flex flex-1 flex-col min-h-0">
        @csrf
        <input type="hidden" name="verification_method" value="id_only">
        <input type="hidden" name="proof_mode" id="idv_proof_mode" value="">
        <input type="hidden" name="id_front_base64" id="id_front_base64">
        <input type="hidden" name="id_back_base64" id="id_back_base64">
        <input type="hidden" name="face_selfie_base64" id="face_selfie_base64">
        <input type="hidden" name="ocr_edited_json" id="idv_ocr_edited_json">
        <input type="hidden" name="latitude" id="idv_latitude">
        <input type="hidden" name="longitude" id="idv_longitude">
        <input type="hidden" name="geo_accuracy" id="idv_geo_accuracy">

        <header
            class="idv-header-when-not-upload absolute inset-x-0 top-0 z-[30] flex items-center gap-2 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 bg-black border-b border-white/10"
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
                    <option value="student_id">Student ID</option>
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
            class="hidden absolute inset-x-0 bottom-0 z-[30] flex flex-col items-center gap-4 px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] bg-gradient-to-t from-black via-black/95 to-transparent"
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

        <div id="idvConfirmModal" class="hidden absolute inset-0 z-[280] items-center justify-center bg-black/70 p-4">
            <div class="w-full max-w-sm glass rounded-2xl border border-white/10 p-5 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-white">Confirm information</h3>
                <p class="mt-2 text-sm text-slate-300">Please confirm your verification information is correct before submitting.</p>
                <div class="mt-4 rounded-lg border border-white/10 bg-white/5 p-3 text-xs text-slate-200 space-y-1">
                    <p><span class="text-slate-400">Mode:</span> <span id="idvConfirmMode">-</span></p>
                    <p><span class="text-slate-400">ID type:</span> <span id="idvConfirmIdType">-</span></p>
                    <p><span class="text-slate-400">ID front:</span> <span id="idvConfirmFront">-</span></p>
                    <p><span class="text-slate-400">Selfie:</span> <span id="idvConfirmSelfie">-</span></p>
                </div>
                <div class="mt-3 rounded-lg border border-white/10 bg-black/20 p-3">
                    <p class="text-xs font-medium text-white">OCR status</p>
                    <p id="idvConfirmOcrStatus" class="mt-1 text-xs text-slate-300">Checking OCR…</p>
                    <div id="idvOcrEditForm" class="mt-3 grid grid-cols-1 gap-2 text-xs"></div>
                    <div id="idvConfirmOcrFields" class="mt-2 space-y-1 text-xs text-slate-200"></div>
                </div>
                <div class="mt-5 flex gap-3">
                    <button type="button" id="idvConfirmCancel" class="btn-secondary flex-1 py-2.5 text-sm">Cancel</button>
                    <button type="button" id="idvConfirmProceed" class="btn-primary flex-1 py-2.5 text-sm">Submit</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(() => {
    const LS_AUTO = 'idv_auto_capture';
    const form = document.getElementById('idVerificationForm');
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
    const confirmModal = document.getElementById('idvConfirmModal');
    const confirmCancelBtn = document.getElementById('idvConfirmCancel');
    const confirmProceedBtn = document.getElementById('idvConfirmProceed');
    const confirmMode = document.getElementById('idvConfirmMode');
    const confirmIdType = document.getElementById('idvConfirmIdType');
    const confirmFront = document.getElementById('idvConfirmFront');
    const confirmSelfie = document.getElementById('idvConfirmSelfie');
    const confirmOcrStatus = document.getElementById('idvConfirmOcrStatus');
    const confirmOcrFields = document.getElementById('idvConfirmOcrFields');
    const ocrEditForm = document.getElementById('idvOcrEditForm');
    const ocrEditedJsonInput = document.getElementById('idv_ocr_edited_json');
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
        back: document.getElementById('id_back_base64'),
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
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const ocrPreviewEndpoint = @json(route('driver-verification.ocr-preview'));
    const ocrReviewRoute = @json(route('verification.id-ocr-review'));
    const ocrReviewStorageKey = 'idv_ocr_review_payload';
    const detector = ('FaceDetector' in window) ? new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 }) : null;

    const steps = [
        { key: 'front', label: 'ID card front', title: 'Step 1 of 2: Capture ID front', hint: 'Hold the front side of your ID inside the rectangle frame.', guide: 'id' },
        { key: 'selfie', label: 'Selfie with ID', title: 'Step 2 of 2: Capture selfie with ID', hint: 'Keep your face in the circle and ID visible in frame.', guide: 'face' },
    ];
    const MOBILE_OCR_MAX_BYTES = 950 * 1024;

    function setHint(text) {
        if (hint) hint.textContent = text;
    }

    async function fileToDataUrl(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(String(reader.result || ''));
            reader.onerror = () => reject(new Error('read_failed'));
            reader.readAsDataURL(file);
        });
    }

    async function compressImageForOcr(file, maxBytes = MOBILE_OCR_MAX_BYTES) {
        const originalDataUrl = await fileToDataUrl(file);
        if (file.size <= maxBytes) {
            return originalDataUrl;
        }

        const image = new Image();
        await new Promise((resolve, reject) => {
            image.onload = () => resolve(true);
            image.onerror = () => reject(new Error('image_load_failed'));
            image.src = originalDataUrl;
        });

        const workCanvas = document.createElement('canvas');
        const ctx = workCanvas.getContext('2d');
        if (!ctx) {
            return originalDataUrl;
        }

        const scales = [1, 0.9, 0.8, 0.7, 0.6];
        for (const scale of scales) {
            workCanvas.width = Math.max(1, Math.round(image.width * scale));
            workCanvas.height = Math.max(1, Math.round(image.height * scale));
            ctx.drawImage(image, 0, 0, workCanvas.width, workCanvas.height);
            for (const quality of [0.85, 0.75, 0.65, 0.55, 0.45]) {
                const dataUrl = workCanvas.toDataURL('image/jpeg', quality);
                const estimatedBytes = Math.ceil((dataUrl.length - dataUrl.indexOf(',') - 1) * 3 / 4);
                if (estimatedBytes <= maxBytes) {
                    return dataUrl;
                }
            }
        }

        return workCanvas.toDataURL('image/jpeg', 0.4);
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
            zoneEl.style.borderColor = good ? 'rgba(34, 197, 92, 0.98)' : 'rgba(239, 68, 68, 0.95)';
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

    function resetConfirmOcrView(text = 'Checking OCR…') {
        if (confirmOcrStatus) confirmOcrStatus.textContent = text;
        if (confirmOcrFields) confirmOcrFields.innerHTML = '';
        if (ocrEditForm) ocrEditForm.innerHTML = '';
    }

    function formatFieldValue(value) {
        if (value === null || value === undefined) return '';
        if (Array.isArray(value)) {
            return value.map((entry) => String(entry ?? '')).join('<br>');
        }
        if (typeof value === 'object') {
            return Object.entries(value)
                .map(([k, v]) => String(k).replace(/_/g, ' ') + ': ' + String(v ?? ''))
                .join('<br>');
        }
        return String(value);
    }

    function labelForKey(key) {
        if (key === 'gender') return 'sex';
        return String(key).replace(/_/g, ' ');
    }

    function appendOcrField(key, value) {
        if (!confirmOcrFields) return;
        const p = document.createElement('p');
        p.className = 'mt-1';
        p.innerHTML = '<span class="text-slate-400">' + labelForKey(key) + ':</span><br><span class="text-slate-100">' + formatFieldValue(value) + '</span>';
        confirmOcrFields.appendChild(p);
    }

    function renderPriorityFields(fields, ocrRawText) {
        if (!confirmOcrFields) return;
        confirmOcrFields.innerHTML = '';
        const priority = [
            'id_type',
            'detected_language',
            'id_number',
            'last_name',
            'first_name',
            'middle_name',
            'birthdate',
            'gender',
            'address',
            'date_of_issuance',
            'expiry_date',
        ];
        const shown = new Set();
        priority.forEach((key) => {
            if (Object.prototype.hasOwnProperty.call(fields, key) && fields[key]) {
                appendOcrField(key, fields[key]);
                shown.add(key);
            }
        });

        Object.entries(fields).forEach(([key, value]) => {
            if (shown.has(key) || ['all_text_lines', 'raw_text', 'key_values', 'date_values', 'important_lines', 'name_line', 'full_name'].includes(key)) {
                return;
            }
            appendOcrField(key, value);
        });

        // Keep UI concise: avoid dumping full noisy OCR text by default.
    }

    function confidenceColorClass(confidence) {
        if (confidence >= 0.7) return 'border-green-500/70 bg-green-500/10';
        if (confidence >= 0.4) return 'border-yellow-500/70 bg-yellow-500/10';
        return 'border-red-500/70 bg-red-500/10';
    }

    function confidenceText(confidence) {
        return Number(confidence || 0).toFixed(2);
    }

    function renderEditableOcrFields(fields) {
        if (!ocrEditForm) return;
        ocrEditForm.innerHTML = '';
        const editableKeys = [
            'id_type',
            'id_number',
            'first_name',
            'middle_name',
            'last_name',
            'birthdate',
            'gender',
            'address',
            'date_of_issuance',
            'expiry_date',
        ];

        editableKeys.forEach((key) => {
            const raw = fields[key];
            let value = '';
            let confidence = 0;
            if (raw && typeof raw === 'object' && Object.prototype.hasOwnProperty.call(raw, 'value')) {
                value = String(raw.value || '');
                confidence = Number(raw.confidence || 0);
            } else if (typeof raw === 'string') {
                value = raw;
                confidence = 0.5;
            }

            const wrap = document.createElement('div');
            wrap.className = 'rounded-md border p-2 ' + confidenceColorClass(confidence);
            wrap.innerHTML = ''
                + '<label class="block text-[11px] text-slate-200 mb-1">' + labelForKey(key) + ' '
                + '<span class="text-slate-400">(conf: ' + confidenceText(confidence) + ')</span></label>'
                + '<input type="text" data-ocr-edit="' + key + '" value="' + value.replace(/"/g, '&quot;') + '" '
                + 'class="w-full rounded bg-black/30 border border-white/20 text-slate-100 px-2 py-1 text-xs">';
            ocrEditForm.appendChild(wrap);
        });
    }

    function captureEditedOcrData() {
        if (!ocrEditedJsonInput) return;
        const edits = {};
        document.querySelectorAll('[data-ocr-edit]').forEach((el) => {
            const key = el.getAttribute('data-ocr-edit');
            const value = String(el.value || '').trim();
            if (key && value) {
                edits[key] = value;
            }
        });
        ocrEditedJsonInput.value = JSON.stringify(edits);
    }

    function renderConfirmStaticDetails() {
        const modeText = proofMode === 'upload_file' ? 'Upload ID files' : 'Selfie with ID';
        const idTypeText = proofMode === 'selfie_with_id'
            ? 'Auto detect via OCR'
            : (idTypeSelect?.selectedOptions?.[0]?.textContent?.trim() || 'Not selected');
        if (confirmMode) confirmMode.textContent = modeText;
        if (confirmIdType) confirmIdType.textContent = idTypeText;
        if (confirmFront) {
            const hasFront = proofMode === 'upload_file'
                ? !!(uploadFrontInput?.files && uploadFrontInput.files.length > 0)
                : !!inputs.front.value;
            confirmFront.textContent = hasFront ? 'Provided' : 'Missing';
        }
        if (confirmSelfie) {
            const hasSelfie = proofMode === 'selfie_with_id' ? !!inputs.selfie.value : false;
            confirmSelfie.textContent = proofMode === 'selfie_with_id' ? (hasSelfie ? 'Provided' : 'Missing') : 'Not required';
        }
    }

    async function loadOcrPreviewForConfirmation() {
        resetConfirmOcrView('Checking OCR…');
        const formData = new FormData();
        formData.append('proof_mode', proofMode || 'upload_file');
        formData.append('id_type', proofMode === 'selfie_with_id' ? 'ocr_auto_detect' : (idTypeSelect?.value || 'other'));
        if (proofMode === 'upload_file') {
            if (inputs.front.value) {
                formData.append('id_front_base64', inputs.front.value);
            } else if (uploadFrontInput?.files && uploadFrontInput.files.length > 0) {
                formData.append('id_front_file', uploadFrontInput.files[0]);
            }
        } else if (inputs.front.value) {
            formData.append('id_front_base64', inputs.front.value);
        }

        try {
            const res = await fetch(ocrPreviewEndpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: formData,
            });
            const payload = await res.json();
            if (!res.ok || payload.status !== 'ok') {
                if (confirmOcrStatus) confirmOcrStatus.textContent = 'OCR failed or unavailable.';
                return null;
            }
            const ocr = payload.ocr || {};
            const status = String(ocr.status || 'unknown');
            if (status !== 'ok') {
                const reason = String(ocr.reason || 'unknown');
                const message = String(ocr.message || '');
                let statusText = 'OCR status: ' + status;
                if (reason) statusText += ' (' + reason.replace(/_/g, ' ') + ')';
                if (message) statusText += '. ' + message;
                if (confirmOcrStatus) confirmOcrStatus.textContent = statusText;
                return null;
            }
            const fields = (ocr.fields && typeof ocr.fields === 'object') ? ocr.fields : {};
            const entries = Object.entries(fields);
            if (entries.length === 0) {
                if (confirmOcrStatus) confirmOcrStatus.textContent = 'OCR worked, but no clear fields were extracted.';
                if (ocr.raw_text) {
                    appendOcrField('raw_text', ocr.raw_text);
                }
                return ocr;
            }
            if (confirmOcrStatus) confirmOcrStatus.textContent = 'OCR worked. Please review important extracted fields:';
            renderEditableOcrFields(fields);
            renderPriorityFields(fields, ocr.raw_text || '');
            return ocr;
        } catch (_err) {
            if (confirmOcrStatus) confirmOcrStatus.textContent = 'OCR failed or unavailable.';
            return null;
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
    uploadFrontInput?.addEventListener('change', async () => {
        if (uploadFrontInput?.files && uploadFrontInput.files.length > 0) {
            try {
                setHint('Optimizing ID image for OCR...');
                inputs.front.value = await compressImageForOcr(uploadFrontInput.files[0]);
            } catch (_err) {
                inputs.front.value = '';
            }
        } else {
            inputs.front.value = '';
        }
        refreshSubmit();
    });
    const uploadBackInput = document.getElementById('idv_upload_back');
    uploadBackInput?.addEventListener('change', async () => {
        if (uploadBackInput?.files && uploadBackInput.files.length > 0) {
            try {
                inputs.back.value = await compressImageForOcr(uploadBackInput.files[0]);
            } catch (_err) {
                inputs.back.value = '';
            }
        } else {
            inputs.back.value = '';
        }
    });
    enableBtn?.addEventListener('click', () => {
        if (proofMode === 'upload_file') return;
        startCamera();
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const hasFront = proofMode === 'upload_file'
            ? !!inputs.front.value
            : !!inputs.front.value;
        if (!hasFront) {
            setHint('Please provide ID front image first.');
            return;
        }
        setHint('Preparing OCR review...');
        const ocr = await loadOcrPreviewForConfirmation();
        const reviewPayload = {
            basePayload: {
                proof_mode: proofMode || 'upload_file',
                id_type: proofMode === 'selfie_with_id' ? 'ocr_auto_detect' : (idTypeSelect?.value || 'other'),
                id_front_base64: inputs.front.value || '',
                id_back_base64: inputs.back.value || '',
                face_selfie_base64: inputs.selfie.value || '',
            },
            ocr: ocr || { status: 'error', fields: {} },
        };
        sessionStorage.setItem(ocrReviewStorageKey, JSON.stringify(reviewPayload));
        window.location.href = ocrReviewRoute;
    });

    confirmCancelBtn?.addEventListener('click', () => {
        confirmModal?.classList.add('hidden');
        confirmModal?.classList.remove('flex');
    });

    confirmProceedBtn?.addEventListener('click', () => {
        confirmModal?.classList.add('hidden');
        confirmModal?.classList.remove('flex');
        if (!form) return;
        captureEditedOcrData();
        form.dataset.confirmed = 'true';
        form.requestSubmit();
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
