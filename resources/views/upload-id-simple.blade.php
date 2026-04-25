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

                <!-- ID Confirmation Section -->
                <div id="idvIdConfirmation" class="hidden mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">Confirm ID Information</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Type:</label>
                            <input type="text" id="idv_confirmed_type" class="form-input text-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Extracted Text:</label>
                            <textarea id="idv_extracted_text" class="form-input text-sm" rows="4" readonly></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button type="button" id="idv_confirm_id" class="btn-primary flex-1 py-2.5 text-sm">Confirm & Continue</button>
                        <button type="button" id="idv_retry_id" class="btn-secondary flex-1 py-2.5 text-sm">Retry OCR</button>
                        <button type="button" id="idv_cancel_id" class="btn-danger flex-1 py-2.5 text-sm">Cancel</button>
                    </div>
                </div>

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
    const confirmationSection = document.getElementById('idvIdConfirmation');
    const extractedTextInput = document.getElementById('idv_extracted_text');
    const confirmedTypeInput = document.getElementById('idv_confirmed_type');
    const confirmIdBtn = document.getElementById('idv_confirm_id');
    const retryIdBtn = document.getElementById('idv_retry_id');
    const cancelIdBtn = document.getElementById('idv_cancel_id');

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

    // ID confirmation functionality
    async function processIdConfirmation(imageData) {
        try {
            setHint('Processing OCR... Please wait.');

            // Show loading state
            extractedTextInput.value = 'Processing OCR...';

            // Set ID type from dropdown
            if (idTypeSelect && confirmedTypeInput) {
                confirmedTypeInput.value = idTypeSelect.options[idTypeSelect.selectedIndex]?.text || 'Unknown';
            }

            // Simulate OCR processing (in real implementation, this would call backend)
            setTimeout(() => {
                extractedTextInput.value = 'OCR processing complete. Please review the extracted text and confirm.';
                setHint('OCR processing complete. Please confirm ID information.');
            }, 2000);
        } catch (error) {
            console.error('ID confirmation error:', error);
            extractedTextInput.value = 'OCR processing failed. Please verify manually.';
            setHint('OCR processing failed. Please try again or submit manually.');
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

            // Show confirmation dialog with OCR processing
            confirmationSection.classList.remove('hidden');

            // Process OCR for uploaded file
            if (imageData) {
                processOcrConfirmation(imageData);
            }
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

    // Event listeners for ID confirmation buttons
    confirmIdBtn?.addEventListener('click', () => {
        // Store confirmed ID data in hidden inputs for form submission
        const form = document.getElementById('idVerificationForm');

        // Add confirmed ID data as hidden inputs
        const confirmedText = document.createElement('input');
        confirmedText.type = 'hidden';
        confirmedText.name = 'idv_confirmed_text';
        confirmedText.value = extractedTextInput.value;
        form.appendChild(confirmedText);

        const confirmedType = document.createElement('input');
        confirmedType.type = 'hidden';
        confirmedType.name = 'idv_confirmed_type';
        confirmedType.value = confirmedTypeInput.value;
        form.appendChild(confirmedType);

        // Submit the form
        form.submit();
    });

    retryIdBtn?.addEventListener('click', () => {
        const imageData = inputs.front.value;
        if (imageData) {
            processIdConfirmation(imageData);
        }
    });

    cancelIdBtn?.addEventListener('click', () => {
        confirmationSection.classList.add('hidden');
        setHint('ID confirmation cancelled. You can still submit your ID.');
    });

    // Initialize
    if (idTypeSelect) {
        idTypeSelect.value = 'philsys_national_id';
    }
    refreshSubmit();
})();
</script>
@endsection
