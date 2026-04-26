@extends('layouts.app')

@section('content')
<div class="fixed inset-0 z-[2147483647] bg-slate-950 text-white overflow-y-auto">
    <div class="h-screen px-4 py-4 flex flex-col" style="padding-top: max(1rem, env(safe-area-inset-top)); padding-bottom: max(6rem, env(safe-area-inset-bottom));">
        <div class="flex-1 flex flex-col justify-center">
            <div class="mx-auto w-full max-w-md">
                <div class="glass rounded-2xl border border-white/10 p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold">Review OCR Fields</h2>
                    <a href="{{ route('verification.id') }}" class="text-xs text-slate-300 hover:text-white">Back</a>
                </div>
                <p class="mt-1 text-xs text-slate-400">Edit fields before final submission. Colors indicate confidence.</p>

                <form id="idvOcrReviewForm" method="POST" action="{{ route('driver-verification.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="verification_method" value="id_only">
                    <input type="hidden" name="proof_mode" id="rv_proof_mode">
                    <input type="hidden" name="id_type" id="rv_id_type">
                    <input type="hidden" name="id_front_base64" id="rv_id_front_base64">
                    <input type="hidden" name="id_back_base64" id="rv_id_back_base64">
                    <input type="hidden" name="face_selfie_base64" id="rv_face_selfie_base64">
                    <input type="hidden" name="ocr_edited_json" id="rv_ocr_edited_json">

                    <div id="rvFields" class="space-y-2"></div>

                    <div class="grid grid-cols-1 gap-2 pt-2">
                        <button type="submit" class="btn-primary w-full py-2.5 text-sm">Submit Verification</button>
                        <a href="{{ route('verification.id') }}" class="btn-secondary w-full py-2.5 text-sm text-center">Retake / Re-upload</a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const STORAGE_KEY = 'idv_ocr_review_payload';
    const payloadRaw = sessionStorage.getItem(STORAGE_KEY);
    if (!payloadRaw) {
        window.location.href = '{{ route('verification.id') }}';
        return;
    }

    let payload = null;
    try {
        payload = JSON.parse(payloadRaw);
    } catch (_e) {
        window.location.href = '{{ route('verification.id') }}';
        return;
    }

    const base = payload.basePayload || {};
    const fields = (payload.ocr && payload.ocr.fields && typeof payload.ocr.fields === 'object') ? payload.ocr.fields : {};
    const form = document.getElementById('idvOcrReviewForm');
    const fieldsWrap = document.getElementById('rvFields');

    document.getElementById('rv_proof_mode').value = String(base.proof_mode || 'upload_file');
    document.getElementById('rv_id_type').value = String(base.id_type || 'other');
    document.getElementById('rv_id_front_base64').value = String(base.id_front_base64 || '');
    document.getElementById('rv_id_back_base64').value = String(base.id_back_base64 || '');
    document.getElementById('rv_face_selfie_base64').value = String(base.face_selfie_base64 || '');

    const editableKeys = [
        'id_type',
        'id_number',
        'first_name',
        'middle_name',
        'last_name',
        'birthdate',
        'gender',
        'address',
        'birthplace',
        'civil_status',
    ];

    function confidenceClass(score) {
        if (score >= 0.7) return 'border-green-500/70 bg-green-500/10';
        if (score >= 0.4) return 'border-yellow-500/70 bg-yellow-500/10';
        return 'border-red-500/70 bg-red-500/10';
    }

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
        } else if (key === 'id_type' && typeof fields.id_type === 'string') {
            value = fields.id_type;
        }

        const div = document.createElement('div');
        div.className = 'rounded-lg border p-2 ' + confidenceClass(confidence);
        div.innerHTML = ''
            + '<label class="block text-[11px] text-slate-200 mb-1">' + key.replace(/_/g, ' ')
            + ' <span class="text-slate-400">(conf: ' + confidence.toFixed(2) + ')</span></label>'
            + '<input type="text" data-edit-key="' + key + '" value="' + value.replace(/"/g, '&quot;') + '" '
            + 'class="w-full rounded bg-black/30 border border-white/20 text-slate-100 px-2 py-1 text-xs">';
        fieldsWrap.appendChild(div);
    });

    form?.addEventListener('submit', () => {
        const edited = {};
        document.querySelectorAll('[data-edit-key]').forEach((el) => {
            const key = el.getAttribute('data-edit-key');
            const value = String(el.value || '').trim();
            if (key) edited[key] = value;
        });
        document.getElementById('rv_ocr_edited_json').value = JSON.stringify(edited);
        sessionStorage.removeItem(STORAGE_KEY);
    });
})();
</script>
@endsection

