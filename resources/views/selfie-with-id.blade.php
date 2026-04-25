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
        <input type="hidden" name="proof_mode" id="idv_proof_mode" value="selfie_with_id">
        <input type="hidden" name="id_front_base64" id="id_front_base64">
        <input type="hidden" name="id_back_base64" id="id_back_base64">
        <input type="hidden" name="face_selfie_base64" id="face_selfie_base64">
        <input type="hidden" name="latitude" id="idv_latitude">
        <input type="hidden" name="longitude" id="idv_longitude">
        <input type="hidden" name="geo_accuracy" id="idv_geo_accuracy">

        <header
            class="flex shrink-0 items-center gap-2 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 bg-gradient-to-b from-slate-900/90 to-transparent border-b border-white/5"
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
                <p id="idvHint" class="text-xs text-slate-400 truncate mt-0.5">Position your ID and face in the frame.</p>
            </div>
        </header>

        {{-- Main camera block --}}
        <div id="idvMainCameraBlock" class="flex-1 min-h-0 flex flex-col overflow-y-auto px-4 py-6" style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));">
            <div class="w-full max-w-md mx-auto glass p-5 sm:p-6 rounded-2xl border border-white/10">
                <div class="relative aspect-[4/3] bg-black rounded-xl overflow-hidden mb-4">
                    <video id="idvVideo" class="absolute inset-0 w-full h-full object-cover" autoplay muted playsinline></video>
                    <img id="idvPreviewImg" class="absolute inset-0 w-full h-full object-cover hidden" alt="Captured">
                    <canvas id="idvCanvas" class="hidden"></canvas>
                    
                    {{-- Guide overlay --}}
                    <div id="idvGuide" class="absolute inset-0 pointer-events-none">
                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 75" preserveAspectRatio="none">
                            <!-- ID card frame -->
                            <rect x="10" y="5" width="35" height="22" fill="none" stroke="rgba(59, 130, 246, 0.5)" stroke-width="0.5" stroke-dasharray="2 1"/>
                            <text x="27.5" y="3" fill="rgba(59, 130, 246, 0.8)" font-size="2" text-anchor="middle" font-weight="600">ID CARD</text>
                            
                            <!-- Face frame -->
                            <rect x="55" y="20" width="35" height="45" fill="none" stroke="rgba(34, 197, 94, 0.5)" stroke-width="0.5" stroke-dasharray="2 1"/>
                            <text x="72.5" y="18" fill="rgba(34, 197, 94, 0.8)" font-size="2" text-anchor="middle" font-weight="600">FACE</text>
                        </svg>
                    </div>
                </div>

                <div class="space-y-3">
                    <button type="button" id="idvCapture" class="btn-primary w-full py-3 text-sm flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Capture
                    </button>
                    
                    <button type="button" id="idvRetakeBtn" class="btn-secondary w-full py-3 text-sm hidden">
                        Retake
                    </button>
                    
                    <button type="submit" id="idvSubmit" class="btn-primary w-full py-3 text-sm hidden">
                        Submit verification
                    </button>
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

    const inputs = {
        front: document.getElementById('id_front_base64'),
        selfie: document.getElementById('face_selfie_base64'),
    };

    let stream = null;
    let mode = 'live';
    let stepIndex = 0;
    const steps = [
        { key: 'front', label: 'ID Card' },
        { key: 'selfie', label: 'Selfie with ID' }
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
        if (stepIndex < steps.length) {
            stepTitle.textContent = `Step ${stepIndex + 1} of ${steps.length}: ${steps[stepIndex].label}`;
        }
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

    function captureFrame() {
        if (!video.videoWidth) {
            setHint('Start the camera first.');
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
            setHint(`Saved ${step.label}. Continue to ${steps[stepIndex].label}.`);
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

    // Initialize
    syncStepUi();
    startCamera();
    window.addEventListener('beforeunload', stopCamera);
})();
</script>
@endsection
