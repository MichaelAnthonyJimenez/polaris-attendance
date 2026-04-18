@extends('layouts.app')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="min-w-0">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white leading-tight">Driver Verification Requests</h1>
        <p class="text-slate-400 mt-1.5 sm:mt-2 text-sm sm:text-base">Review and manage driver verification requests</p>
    </div>

    <div class="glass p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-4 border-b border-white/10">
            <p id="verificationSelectedCount" class="text-sm text-slate-300">0 selected</p>
            <div class="w-full sm:w-auto">
                <label for="verificationBulkActionDropdown" class="sr-only">Bulk action menu</label>
                <select id="verificationBulkActionDropdown" class="form-select w-full sm:min-w-[16rem] text-sm" autocomplete="off">
                    <option value="" selected>Bulk actions...</option>
                    <option value="select_all">Select all on this page</option>
                    <option value="clear_selection">Clear selection</option>
                    <option value="approve">Approve selected</option>
                    <option value="reject">Reject selected</option>
                    <option value="delete">Delete selected</option>
                </select>
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            @forelse ($verifications as $verification)
                @php
                    $manualDataRaw = $verification->manual_form_data;
                    $manualData = is_array($manualDataRaw)
                        ? $manualDataRaw
                        : (is_string($manualDataRaw) ? (json_decode($manualDataRaw, true) ?? []) : []);
                @endphp
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3 sm:p-4">
                    <div class="flex items-start gap-3 min-w-0">
                        <input
                            type="checkbox"
                            class="verification-cb mt-1 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/50 w-4 h-4 shrink-0"
                            value="{{ $verification->id }}"
                            data-status="{{ $verification->status }}"
                            aria-label="Select verification {{ $verification->id }}"
                        >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2 min-w-0">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-white truncate">
                                        {{ $verification->user?->name ?? $manualData['name'] ?? 'Unknown' }}
                                    </div>
                                    <div class="text-xs text-slate-400 break-all mt-0.5">
                                        {{ $verification->user?->email ?? $manualData['email'] ?? '—' }}
                                    </div>
                                </div>
                                @if($verification->status === 'pending')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-200 border border-yellow-500/40 shrink-0">Pending</span>
                                @elseif($verification->status === 'approved')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-200 border border-emerald-500/40 shrink-0">Approved</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-200 border border-red-500/40 shrink-0">Rejected</span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                @if($verification->verification_method)
                                    <span class="px-2.5 py-1 rounded-full font-medium bg-blue-500/20 text-blue-200 border border-blue-500/40">
                                        {{ ucfirst(str_replace('_', ' ', $verification->verification_method)) }}
                                    </span>
                                @endif
                                <span class="text-slate-400">{{ $verification->created_at?->format('M d, Y') ?? '—' }}</span>
                            </div>
                            @if($verification->reviewer?->name)
                                <p class="mt-2 text-xs text-slate-500">Reviewed by {{ $verification->reviewer->name }}</p>
                            @endif

                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 border-t border-white/10 pt-3">
                                <a href="{{ route('driver-verification.show', $verification) }}" class="text-blue-400 hover:text-blue-300 text-sm font-medium">View</a>
                                @if($verification->status === 'pending')
                                    <button type="button" class="text-emerald-400 hover:text-emerald-300 text-sm font-medium list-single-approve" data-approve-url="{{ route('driver-verification.approve', $verification) }}">Approve</button>
                                    <button type="button" class="text-rose-400 hover:text-rose-300 text-sm font-medium list-single-reject" data-reject-url="{{ route('driver-verification.reject', $verification) }}">Reject</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400">No verification requests found.</div>
            @endforelse
        </div>

        <div class="hidden md:block overflow-x-auto -mx-1 px-1">
            <table class="table-glass min-w-[760px] w-full">
                <thead>
                    <tr>
                        <th class="w-10 px-2">
                            <span class="sr-only">Select</span>
                        </th>
                        <th>Driver</th>
                        <th>Email</th>
                        <th>Verification Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Reviewed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($verifications as $verification)
                        @php
                            $manualDataRaw = $verification->manual_form_data;
                            $manualData = is_array($manualDataRaw)
                                ? $manualDataRaw
                                : (is_string($manualDataRaw) ? (json_decode($manualDataRaw, true) ?? []) : []);
                        @endphp
                        <tr>
                            <td class="align-top pt-4 px-2">
                                <input
                                    type="checkbox"
                                    class="verification-cb rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/50 w-4 h-4"
                                    value="{{ $verification->id }}"
                                    data-status="{{ $verification->status }}"
                                    aria-label="Select verification {{ $verification->id }}"
                                >
                            </td>
                            <td class="font-medium">
                                {{ $verification->user?->name ?? $manualData['name'] ?? 'Unknown' }}
                            </td>
                            <td>{{ $verification->user?->email ?? $manualData['email'] ?? '—' }}</td>
                            <td>
                                @if($verification->verification_method)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-200 border border-blue-500/40">
                                        {{ ucfirst(str_replace('_', ' ', $verification->verification_method)) }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($verification->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-200 border border-yellow-500/40">
                                        Pending
                                    </span>
                                @elseif($verification->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-200 border border-emerald-500/40">
                                        Approved
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-200 border border-red-500/40">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td>{{ $verification->created_at?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $verification->reviewer?->name ?? '—' }}</td>
                            <td>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('driver-verification.show', $verification) }}" class="text-blue-400 hover:text-blue-300 text-sm">View</a>
                                    @if($verification->status === 'pending')
                                        <button type="button" class="text-emerald-400 hover:text-emerald-300 text-sm list-single-approve" data-approve-url="{{ route('driver-verification.approve', $verification) }}">Approve</button>
                                        <button type="button" class="text-rose-400 hover:text-rose-300 text-sm list-single-reject" data-reject-url="{{ route('driver-verification.reject', $verification) }}">Reject</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">No verification requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($verifications->hasPages())
        <div class="glass p-4 sm:p-6 overflow-x-auto pb-2">
            {{ $verifications->links() }}
        </div>
    @endif
</div>

{{-- Hidden bulk forms --}}
<form id="verificationBulkApproveForm" action="{{ route('driver-verification.bulk-approve') }}" method="POST" class="hidden">@csrf</form>
<form id="verificationBulkRejectForm" action="{{ route('driver-verification.bulk-reject') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="reason" id="verificationBulkRejectReasonField" value="">
</form>
<form id="verificationBulkDeleteForm" action="{{ route('driver-verification.bulk-delete') }}" method="POST" class="hidden">@csrf</form>
<form id="verificationSingleApproveForm" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="admin_notes" id="verificationSingleApproveNotesField" value="">
</form>
<form id="verificationSingleRejectForm" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="rejection_reason" id="verificationSingleRejectReasonField" value="">
</form>

{{-- Approve / Reject / Bulk delete modal --}}
<div id="verificationListModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" aria-labelledby="verificationListModalTitle">
    <div class="glass p-4 sm:p-6 rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h3 id="verificationListModalTitle" class="text-lg font-bold text-white mb-2">Confirm</h3>
        <p id="verificationListModalMessage" class="text-slate-400 text-sm mb-4"></p>

        <div id="verificationListModalApproveFields" class="hidden">
            <label class="form-label">Admin Notes (Optional)</label>
            <textarea id="verificationListModalAdminNotes" rows="3" class="form-input w-full" placeholder="Add any notes about this approval..."></textarea>
        </div>

        <div id="verificationListModalRejectFields" class="hidden">
            <label class="form-label">Rejection Reason (Required)</label>
            <textarea id="verificationListModalRejectReason" rows="3" class="form-input w-full" placeholder="Please provide a reason for rejection..."></textarea>
            <p id="verificationListModalRejectError" class="hidden mt-2 text-sm text-red-300">Rejection reason is required.</p>
        </div>

        <div class="flex gap-2 justify-end mt-5 flex-wrap">
            <button type="button" class="btn-secondary" id="verificationListModalCancel">Cancel</button>
            <button type="button" id="verificationListModalPrimary" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('verificationListModal');
    const titleEl = document.getElementById('verificationListModalTitle');
    const msgEl = document.getElementById('verificationListModalMessage');
    const approveFields = document.getElementById('verificationListModalApproveFields');
    const rejectFields = document.getElementById('verificationListModalRejectFields');
    const primaryBtn = document.getElementById('verificationListModalPrimary');
    const rejectErr = document.getElementById('verificationListModalRejectError');
    const adminNotesEl = document.getElementById('verificationListModalAdminNotes');
    const rejectReasonEl = document.getElementById('verificationListModalRejectReason');

    let listContext = { kind: 'idle' };

    function getChecked() {
        return Array.from(document.querySelectorAll('.verification-cb:checked'));
    }

    function getCheckedPendingIds() {
        return getChecked()
            .filter(function (cb) { return cb.dataset.status === 'pending'; })
            .map(function (cb) { return cb.value; });
    }

    function getCheckedAllIds() {
        return getChecked().map(function (cb) { return cb.value; });
    }

    function appendIdsToForm(form, ids) {
        form.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });
        ids.forEach(function (id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        listContext = { kind: 'idle' };
        if (rejectErr) rejectErr.classList.add('hidden');
    }

    function showApproveRejectModal(opts) {
        listContext = opts;
        if (rejectErr) rejectErr.classList.add('hidden');
        if (adminNotesEl) adminNotesEl.value = '';
        if (rejectReasonEl) rejectReasonEl.value = '';

        if (opts.action === 'approve') {
            titleEl.textContent = opts.title || 'Approve verification';
            msgEl.textContent = opts.message || '';
            approveFields.classList.remove('hidden');
            rejectFields.classList.add('hidden');
            primaryBtn.textContent = opts.primaryLabel || 'Approve';
            primaryBtn.className = 'px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium';
        } else {
            titleEl.textContent = opts.title || 'Reject verification';
            msgEl.textContent = opts.message || '';
            rejectFields.classList.remove('hidden');
            approveFields.classList.add('hidden');
            primaryBtn.textContent = opts.primaryLabel || 'Reject';
            primaryBtn.className = 'px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium';
        }
        openModal();
    }

    function showDeleteModal(count) {
        listContext = { kind: 'bulk-delete', count: count };
        titleEl.textContent = 'Delete verification requests';
        msgEl.textContent = 'Permanently delete ' + count + ' selected request(s)? This cannot be undone.';
        approveFields.classList.add('hidden');
        rejectFields.classList.add('hidden');
        primaryBtn.textContent = 'Delete';
        primaryBtn.className = 'px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium';
        openModal();
    }

    document.getElementById('verificationListModalCancel').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    var bulkActionSelect = document.getElementById('verificationBulkActionDropdown');
    var selectedCountEl = document.getElementById('verificationSelectedCount');

    function updateSelectedCount() {
        var checked = document.querySelectorAll('.verification-cb:checked').length;
        if (selectedCountEl) {
            selectedCountEl.textContent = checked + ' selected';
        }
    }

    document.querySelectorAll('.verification-cb').forEach(function (cb) {
        cb.addEventListener('change', updateSelectedCount);
    });

    updateSelectedCount();
    bulkActionSelect.addEventListener('change', function () {
        var action = this.value;
        if (!action) return;

        function resetBulkSelect() {
            bulkActionSelect.selectedIndex = 0;
        }

        if (action === 'select_all') {
            document.querySelectorAll('.verification-cb').forEach(function (cb) {
                cb.checked = true;
            });
            updateSelectedCount();
            resetBulkSelect();
            return;
        }

        if (action === 'clear_selection') {
            document.querySelectorAll('.verification-cb').forEach(function (cb) {
                cb.checked = false;
            });
            updateSelectedCount();
            resetBulkSelect();
            return;
        }

        if (action === 'approve') {
            var idsApprove = getCheckedPendingIds();
            if (!idsApprove.length) {
                alert('Select at least one pending verification request.');
                resetBulkSelect();
                return;
            }
            showApproveRejectModal({
                kind: 'bulk-approve',
                action: 'approve',
                title: 'Approve selected',
                message: 'Approve ' + idsApprove.length + ' verification request(s)?',
                primaryLabel: 'Approve all',
            });
            resetBulkSelect();
            return;
        }

        if (action === 'reject') {
            var idsReject = getCheckedPendingIds();
            if (!idsReject.length) {
                alert('Select at least one pending verification request.');
                resetBulkSelect();
                return;
            }
            showApproveRejectModal({
                kind: 'bulk-reject',
                action: 'reject',
                title: 'Reject selected',
                message: 'Reject ' + idsReject.length + ' verification request(s)?',
                primaryLabel: 'Reject all',
            });
            resetBulkSelect();
            return;
        }

        if (action === 'delete') {
            var idsDel = getCheckedAllIds();
            if (!idsDel.length) {
                alert('Select at least one verification request.');
                resetBulkSelect();
                return;
            }
            showDeleteModal(idsDel.length);
            resetBulkSelect();
        }
    });

    document.querySelectorAll('.list-single-approve').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-approve-url');
            showApproveRejectModal({
                kind: 'single-approve',
                action: 'approve',
                approveUrl: url,
                title: 'Approve verification',
                message: 'Approve this verification request?',
                primaryLabel: 'Approve',
            });
        });
    });

    document.querySelectorAll('.list-single-reject').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-reject-url');
            showApproveRejectModal({
                kind: 'single-reject',
                action: 'reject',
                rejectUrl: url,
                title: 'Reject verification',
                message: 'Reject this verification request? This will mark the request as rejected.',
                primaryLabel: 'Reject',
            });
        });
    });

    primaryBtn.addEventListener('click', function () {
        if (listContext.kind === 'bulk-delete') {
            var ids = getCheckedAllIds();
            var form = document.getElementById('verificationBulkDeleteForm');
            appendIdsToForm(form, ids);
            form.submit();
            return;
        }

        if (listContext.action === 'approve') {
            if (listContext.kind === 'bulk-approve') {
                var idsA = getCheckedPendingIds();
                var formA = document.getElementById('verificationBulkApproveForm');
                appendIdsToForm(formA, idsA);
                formA.submit();
                return;
            }
            if (listContext.kind === 'single-approve') {
                var notes = (adminNotesEl && adminNotesEl.value) ? adminNotesEl.value : '';
                document.getElementById('verificationSingleApproveNotesField').value = notes;
                var formS = document.getElementById('verificationSingleApproveForm');
                formS.action = listContext.approveUrl;
                formS.submit();
                return;
            }
        }

        if (listContext.action === 'reject') {
            var reason = rejectReasonEl ? rejectReasonEl.value.trim() : '';
            if (!reason) {
                if (rejectErr) rejectErr.classList.remove('hidden');
                if (rejectReasonEl) rejectReasonEl.focus();
                return;
            }
            if (rejectErr) rejectErr.classList.add('hidden');

            if (listContext.kind === 'bulk-reject') {
                var idsR = getCheckedPendingIds();
                document.getElementById('verificationBulkRejectReasonField').value = reason;
                var formR = document.getElementById('verificationBulkRejectForm');
                appendIdsToForm(formR, idsR);
                formR.submit();
                return;
            }
            if (listContext.kind === 'single-reject') {
                document.getElementById('verificationSingleRejectReasonField').value = reason;
                var formSr = document.getElementById('verificationSingleRejectForm');
                formSr.action = listContext.rejectUrl;
                formSr.submit();
                return;
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
@endsection
