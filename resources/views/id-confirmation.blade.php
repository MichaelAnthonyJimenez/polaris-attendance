@extends('layouts.app')

@section('content')
@include('components.polaris-geo-camera-js')

<div class="fixed inset-0 z-[100] flex flex-col bg-black text-white">
    <div class="flex-1 relative min-h-0 bg-black">
        <div class="w-full max-w-md mx-auto p-6">
            <div class="glass rounded-2xl border border-white/10 p-6">
                <h2 class="text-xl font-bold text-white mb-6 text-center">Confirm ID Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">ID Type:</label>
                        <input type="text" id="idv_confirmed_type" class="form-input text-sm bg-black/20 border-white/20 text-white" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Extracted Text:</label>
                        <textarea id="idv_extracted_text" class="form-input text-sm bg-black/20 border-white/20 text-white" rows="6" readonly></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" id="idv_confirm_id" class="btn-primary flex-1 py-3 text-sm">Confirm & Submit</button>
                    <button type="button" id="idv_retry_id" class="btn-secondary flex-1 py-3 text-sm">Retry OCR</button>
                    <button type="button" id="idv_cancel_id" class="btn-danger flex-1 py-3 text-sm">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const confirmedTypeInput = document.getElementById('idv_confirmed_type');
    const extractedTextInput = document.getElementById('idv_extracted_text');
    const confirmIdBtn = document.getElementById('idv_confirm_id');
    const retryIdBtn = document.getElementById('idv_retry_id');
    const cancelIdBtn = document.getElementById('idv_cancel_id');

    // Get data from session or URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const idType = urlParams.get('id_type') || '';
    const extractedText = urlParams.get('extracted_text') || '';
    const frontImage = urlParams.get('front_image') || '';

    // Populate form with data
    if (confirmedTypeInput && idType) {
        confirmedTypeInput.value = idType;
    }
    if (extractedTextInput && extractedText) {
        extractedTextInput.value = extractedText;
    }

    // Event listeners
    confirmIdBtn?.addEventListener('click', () => {
        // Create form with confirmed data
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("verification.submit") }}';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        form.appendChild(csrfInput);
        
        // Add verification method
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = 'verification_method';
        methodInput.value = 'id_only';
        form.appendChild(methodInput);
        
        // Add confirmed ID data
        const confirmedText = document.createElement('input');
        confirmedText.type = 'hidden';
        confirmedText.name = 'idv_confirmed_text';
        confirmedText.value = extractedTextInput.value || '';
        form.appendChild(confirmedText);
        
        const confirmedType = document.createElement('input');
        confirmedType.type = 'hidden';
        confirmedType.name = 'idv_confirmed_type';
        confirmedType.value = confirmedTypeInput.value || '';
        form.appendChild(confirmedType);
        
        // Add front image if available
        if (frontImage) {
            const frontInput = document.createElement('input');
            frontInput.type = 'hidden';
            frontInput.name = 'id_front_base64';
            frontInput.value = frontImage;
            form.appendChild(frontInput);
        }
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    });

    retryIdBtn?.addEventListener('click', () => {
        // Go back to upload page to retry
        window.location.href = '{{ route("verification.upload-id") }}';
    });

    cancelIdBtn?.addEventListener('click', () => {
        // Go back to verification options
        window.location.href = '{{ route("verification.required") }}';
    });
})();
</script>
@endsection
