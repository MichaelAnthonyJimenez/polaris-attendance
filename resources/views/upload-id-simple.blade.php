@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

<div
    id="idvShell"
    class="fixed inset-0 z-[2147483647] flex flex-col bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 text-white"
    style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100vw; height: 100vh;"
>
    <form method="POST" action="{{ route('driver-verification.store') }}" id="idVerificationForm" class="flex flex-1 flex-col min-h-0" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="verification_method" value="id_only">
        <input type="hidden" name="proof_mode" id="idv_proof_mode" value="upload_file">
        <input type="hidden" name="id_front_base64" id="id_front_base64">
        <input type="hidden" name="id_back_base64" id="id_back_base64">
        <input type="hidden" name="latitude" id="idv_latitude">
        <input type="hidden" name="longitude" id="idv_longitude">
        <input type="hidden" name="geo_accuracy" id="idv_geo_accuracy">

        <header
            class="flex shrink-0 items-center gap-2 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 bg-slate-900/50 border-b border-white/5"
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
                <p class="text-sm font-semibold text-white truncate">Upload ID Files</p>
                <p id="idvHint" class="text-xs text-slate-400 truncate mt-0.5">Upload your ID documents.</p>
            </div>
        </header>

        {{-- Upload only block --}}
        <div id="idvUploadOnlyBlock" class="flex-1 min-h-0 flex flex-col overflow-y-auto px-4 py-6" style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));">
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
    </form>
</div>

<script>
(() => {
    const uploadFrontInput = document.getElementById('idv_upload_front');
    const uploadBackInput = document.getElementById('idv_upload_back');
    const uploadSubmitBtn = document.getElementById('idvUploadSubmit');
    const idTypeSelect = document.getElementById('idv_id_type');
    const hint = document.getElementById('idvHint');

    const inputs = {
        front: document.getElementById('id_front_base64'),
        back: document.getElementById('id_back_base64'),
    };

    function setHint(text) {
        if (hint) hint.textContent = text;
    }

    function refreshSubmit() {
        const hasFront = inputs.front.value && inputs.front.value.length > 0;
        const hasFile = uploadFrontInput.files && uploadFrontInput.files.length > 0;
        
        if (hasFront || hasFile) {
            uploadSubmitBtn.disabled = false;
            setHint('Ready to submit verification.');
        } else {
            uploadSubmitBtn.disabled = true;
            setHint('Please upload your ID front image.');
        }
    }

    // Helper function to convert file to base64
    async function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                resolve(reader.result);
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    // File upload event listeners
    uploadFrontInput?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (file) {
            const imageData = await fileToBase64(file);
            inputs.front.value = imageData;
            refreshSubmit();
        }
    });

    uploadBackInput?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (file) {
            const imageData = await fileToBase64(file);
            inputs.back.value = imageData;
            refreshSubmit();
        }
    });

    // Initialize
    if (idTypeSelect) {
        idTypeSelect.value = 'philsys_national_id';
    }
    refreshSubmit();
})();
</script>
@endsection
