@extends('layouts.app')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="glass p-4 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-start">
            <div class="min-w-0 lg:pr-6">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white leading-tight">Verification Request Details</h1>
                <p class="text-slate-400 mt-1.5 sm:mt-2 text-sm sm:text-base">Review driver verification request</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto shrink-0 lg:justify-end">
                @if($verificationRequest->user)
                    <a href="{{ route('users.show', $verificationRequest->user) }}" class="btn-primary text-center text-sm sm:text-base">View User</a>
                @endif
                <a href="{{ route('driver-verification.index') }}" class="btn-secondary text-center text-sm sm:text-base">Back to List</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Request Information -->
        <div class="glass p-4 sm:p-6 space-y-4">
            <h2 class="text-xl font-bold text-white mb-4">Request Information</h2>

            @php
                $manualDataRaw = $verificationRequest->manual_form_data;
                $manualData = is_array($manualDataRaw)
                    ? $manualDataRaw
                    : (is_string($manualDataRaw) ? (json_decode($manualDataRaw, true) ?? []) : []);
                $faceSequence = $manualData['face_sequence'] ?? [];

                /**
                 * Resolve a verification image path that might live either in:
                 * - storage/app/public (served via /storage symlink), OR
                 * - public/ (served directly).
                 *
                 * Returns: [bool $exists, string $url]
                 */
                $resolvePublicOrStorageUrl = $resolvePublicOrStorageUrl ?? function (?string $path): array {
                    if (!is_string($path) || trim($path) === '') {
                        return [false, ''];
                    }

                    $raw = trim($path);
                    $normalized = ltrim($raw, '/\\');

                    // If DB stored "storage/..." or "/storage/...", strip it back to the "public disk" relative path.
                    if (str_starts_with($normalized, 'storage/')) {
                        $normalized = substr($normalized, strlen('storage/'));
                    }

                    // If DB stored "public/..." or "app/public/..." style paths, normalize to disk-relative.
                    foreach (['public/', 'app/public/'] as $prefix) {
                        if (str_starts_with($normalized, $prefix)) {
                            $normalized = substr($normalized, strlen($prefix));
                        }
                    }

                    // Special case: our verification images are stored on the "public" disk
                    // under storage/app/public/verification/... which is exposed via the
                    // /storage symlink. For these, always build the URL as /storage/...
                    // so they render even if any existence checks fail.
                    if (str_starts_with($normalized, 'verification/')) {
                        return [true, asset('storage/' . $normalized)];
                    }

                    // 1) storage/app/public via Storage disk('public') (most common)
                    try {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = \Illuminate\Support\Facades\Storage::disk('public');
                        if ($disk->exists($normalized)) {
                            // Let the disk decide whether to return an absolute or relative URL.
                            $diskUrl = $disk->url($normalized); // usually "/storage/..."
                            return [true, $diskUrl];
                        }
                    } catch (\Throwable $e) {
                        // ignore and fall through to public_path check
                    }

                    // 2) direct public/...
                    $publicFile = public_path($normalized);
                    if (is_file($publicFile)) {
                        return [true, asset($normalized)];
                    }

                    // 3) last-ditch: allow raw path to still try to render (useful for debugging)
                    return [false, asset($normalized)];
                };
            @endphp

            <div>
                <label class="text-sm text-slate-400">Driver Name</label>
                <p class="text-white font-medium">
                    {{ $verificationRequest->driver?->name
                        ?? $verificationRequest->user?->name
                        ?? $manualData['name']
                        ?? $manualData['driver_name']
                        ?? $manualData['full_name']
                        ?? 'Unknown' }}
                </p>
            </div>

            <div>
                <label class="text-sm text-slate-400">Email</label>
                <p class="text-white">
                    {{ $verificationRequest->driver?->email
                        ?? $verificationRequest->user?->email
                        ?? $manualData['email']
                        ?? 'N/A' }}
                </p>
            </div>

            @php
                // Prefer the explicit verification_method (used in forms),
                // fall back to the legacy/internal type field.
                $rawType = $verificationRequest->verification_method ?? $verificationRequest->type;
                $normalizedType = is_string($rawType) ? strtolower($rawType) : null;
            @endphp
            <div>
                <label class="text-sm text-slate-400">Verification Type</label>
                <p class="text-white">
                    @if($normalizedType === 'facial')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-900 border border-blue-500/40">
                            Facial
                        </span>
                    @elseif($normalizedType === 'facial_with_id' || $normalizedType === 'id_with_selfie')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-900 border border-indigo-500/40">
                            Facial + ID
                        </span>
                    @elseif($normalizedType === 'id_only')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-900 border border-yellow-500/40">
                            ID Only
                        </span>
                    @elseif($normalizedType === 'manual')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-900 border border-purple-500/40">
                            Manual
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-500/20 text-slate-900 border border-slate-500/40">
                            {{ $rawType ? ucfirst(str_replace('_', ' ', $rawType)) : '—' }}
                        </span>
                    @endif
                </p>
            </div>

            @php
                $rawStatus = $verificationRequest->status ?? '';
                $status = is_string($rawStatus) ? strtolower($rawStatus) : '';
            @endphp
            <div>
                <label class="text-sm text-slate-400">Status</label>
                <p class="text-white">
                    @if($status === 'pending')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-900 border border-yellow-500/40">
                            Pending
                        </span>
                    @elseif($status === 'approved')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-900 border border-emerald-500/40">
                            Approved
                        </span>
                    @elseif($status === 'rejected')
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-900 border border-red-500/40">
                            Rejected
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-500/20 text-slate-900 border border-slate-500/40">
                            {{ $rawStatus ?: 'Unknown' }}
                        </span>
                    @endif
                </p>
            </div>

            <div>
                <label class="text-sm text-slate-400">Submitted At</label>
                @php
                    // Prefer created_at, fall back to updated_at, then try to infer from face image path
                    $submittedAt = $verificationRequest->created_at ?? $verificationRequest->updated_at;
                    if (!$submittedAt && !empty($faceSequence)) {
                        $firstPath = is_array($faceSequence) ? reset($faceSequence) : null;
                        if ($firstPath && preg_match('#verification/(?:facial/)?(\d{8})/#', $firstPath, $m)) {
                            $submittedAt = \Carbon\Carbon::createFromFormat('Ymd', $m[1]);
                        }
                    }
                @endphp
                <p class="text-white">{{ $submittedAt?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
            </div>

            @if($verificationRequest->reviewer)
                <div>
                    <label class="text-sm text-slate-400">Reviewed By</label>
                    <p class="text-white">{{ $verificationRequest->reviewer->name }}</p>
                </div>

                <div>
                    <label class="text-sm text-slate-400">Reviewed At</label>
                    <p class="text-white">{{ $verificationRequest->reviewed_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                </div>
            @endif

            @if($verificationRequest->reason)
                <div>
                    <label class="text-sm text-slate-400">Reason</label>
                    <p class="text-white bg-white/5 p-3 rounded-lg">{{ $verificationRequest->reason }}</p>
                </div>
            @endif

            @if($verificationRequest->admin_notes)
                <div>
                    <label class="text-sm text-slate-400">Admin Notes</label>
                    <p class="text-white bg-white/5 p-3 rounded-lg">{{ $verificationRequest->admin_notes }}</p>
                </div>
            @endif

            {{-- show manual form entries if they exist --}}
            @if(!empty($manualData))
                @if(isset($manualData['license_number']))
                    <div>
                        <label class="text-sm text-slate-400">License / ID Number</label>
                        <p class="text-white">{{ $manualData['license_number'] }}</p>
                    </div>
                @endif

                @if(isset($manualData['feedback']))
                    <div>
                        <label class="text-sm text-slate-400">Feedback</label>
                        <p class="text-white bg-white/5 p-3 rounded-lg">{{ $manualData['feedback'] }}</p>
                    </div>
                @endif
            @endif

            {{-- Quick actions (pending only) --}}
            @if(($verificationRequest->status ?? null) === 'pending')
                <div class="pt-4 border-t border-white/10">
                    <label class="text-sm text-slate-400">Actions</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <form id="quickApproveForm" action="{{ route('driver-verification.approve', $verificationRequest) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="admin_notes" id="quickApproveNotes" value="">
                            <button type="button" class="btn-primary bg-emerald-600 hover:bg-emerald-700" onclick="openDecisionModal('approve')">Approve</button>
                        </form>
                        <form id="quickRejectForm" action="{{ route('driver-verification.reject', $verificationRequest) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="rejection_reason" id="quickRejectReason" value="">
                            <button type="button" class="btn-primary bg-red-600 hover:bg-red-700" onclick="openDecisionModal('reject')">Reject</button>
                        </form>
                    </div>
                </div>
            @endif

        </div>

        <!-- Verification Images (right side) -->
        <div class="space-y-4 sm:space-y-6">
            {{-- Face captures --}}
            @if(!empty($faceSequence) || $verificationRequest->face_image_path)
                <div class="glass p-4 sm:p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Face Captures</h2>

                    @if(!empty($faceSequence))
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach($faceSequence as $label => $path)
                                @php
                                    [$capExists, $capUrl] = $resolvePublicOrStorageUrl($path);
                                @endphp
                                <div class="rounded-lg overflow-hidden border border-white/10 bg-black/40">
                                    @if($capExists)
                                        <button
                                            type="button"
                                            onclick="openImageLightbox('{{ $capUrl }}', 'Face {{ ucfirst($label) }}')"
                                            class="block w-full text-left cursor-pointer group"
                                        >
                                            <div class="aspect-square w-full bg-black/30 flex items-center justify-center">
                                                <img
                                                    src="{{ $capUrl }}"
                                                    alt="Face {{ ucfirst($label) }}"
                                                    class="w-full h-full object-cover group-hover:opacity-90 transition"
                                                    onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-3 text-center text-red-300 text-xs\'>Failed to load image<br><span class=\'text-slate-400\'>Path: {{ e($path) }}</span></div>';"
                                                >
                                            </div>
                                            <div class="px-2 py-1.5 text-xs text-slate-300 text-center">{{ ucfirst($label) }}</div>
                                        </button>
                                    @else
                                        <div class="aspect-square w-full bg-black/30 flex items-center justify-center">
                                            <span class="text-xs text-slate-400 px-2 text-center">Not found</span>
                                        </div>
                                        <div class="px-2 py-1.5 text-xs text-slate-300 text-center">{{ ucfirst($label) }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        @php
                            [$capExists, $capUrl] = $resolvePublicOrStorageUrl($verificationRequest->face_image_path);
                        @endphp
                        @if($capExists)
                            <button type="button" onclick="openImageLightbox('{{ $capUrl }}', 'Face image')" class="block w-full text-left rounded-lg overflow-hidden border border-white/10 bg-black/40 hover:border-white/20 transition cursor-pointer group">
                                <div class="aspect-video w-full bg-black/30">
                                    <img src="{{ $capUrl }}" alt="Face Image" class="w-full h-full object-cover group-hover:opacity-90 transition">
                                </div>
                                <p class="text-xs text-slate-400 py-2 px-3">Click to view full size</p>
                            </button>
                        @else
                            <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-3">
                                <p class="text-red-300 text-sm">Image file not found</p>
                                <p class="text-slate-400 text-xs mt-1 break-all">Path: {{ $verificationRequest->face_image_path }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            {{-- Selfie with ID (if available) --}}
            @if($verificationRequest->selfie_with_id_path)
                @php
                    [$selfieExists, $selfieUrl] = $resolvePublicOrStorageUrl($verificationRequest->selfie_with_id_path);
                @endphp
                <div class="glass p-4 sm:p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Selfie With ID</h2>
                    @if($selfieExists)
                        <button type="button" onclick="openImageLightbox('{{ $selfieUrl }}', 'Selfie With ID')" class="block w-full text-left rounded-lg overflow-hidden border border-white/10 bg-black/40 hover:border-white/20 transition cursor-pointer group">
                            <img src="{{ $selfieUrl }}" alt="Selfie With ID" class="w-full h-auto object-contain max-h-80 group-hover:opacity-90 transition">
                            <p class="text-xs text-slate-400 py-2 px-3">Click to view full size</p>
                        </button>
                    @else
                        <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-center">
                            <p class="text-red-300 text-sm">Image file not found</p>
                            <p class="text-slate-400 text-xs mt-2 break-all">Path: {{ $verificationRequest->selfie_with_id_path }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ID (Front) --}}
            @if($verificationRequest->id_image_path)
                @php
                    [$idExists, $idUrl] = $resolvePublicOrStorageUrl($verificationRequest->id_image_path);
                @endphp
                <div class="glass p-4 sm:p-6">
                    <h2 class="text-xl font-bold text-white mb-4">ID Image (Front)</h2>
                    @if($idExists)
                        <button type="button" onclick="openImageLightbox('{{ $idUrl }}', 'ID Image (Front)')" class="block w-full text-left rounded-lg overflow-hidden border border-white/10 bg-black/40 hover:border-white/20 transition cursor-pointer group">
                            <img src="{{ $idUrl }}" alt="ID Image (Front)" class="w-full h-auto object-contain max-h-80 group-hover:opacity-90 transition" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-4 text-center text-red-300\'><p>Failed to load image</p><p class=\'text-xs text-slate-400 mt-1\'>Path: {{ $verificationRequest->id_image_path }}</p></div>';">
                            <p class="text-xs text-slate-400 py-2 px-3">Click to view full size</p>
                        </button>
                    @else
                        <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-center">
                            <p class="text-red-300 text-sm">Image file not found</p>
                            <p class="text-slate-400 text-xs mt-2 break-all">Path: {{ $verificationRequest->id_image_path }}</p>
                            <p class="text-slate-400 text-xs">Expected location: storage/app/public/{{ $verificationRequest->id_image_path }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ID (Back) --}}
            @if($verificationRequest->id_image_back_path)
                @php
                    [$idBackExists, $idBackUrl] = $resolvePublicOrStorageUrl($verificationRequest->id_image_back_path);
                @endphp
                <div class="glass p-4 sm:p-6">
                    <h2 class="text-xl font-bold text-white mb-4">ID Image (Back)</h2>
                    @if($idBackExists)
                        <button type="button" onclick="openImageLightbox('{{ $idBackUrl }}', 'ID Image (Back)')" class="block w-full text-left rounded-lg overflow-hidden border border-white/10 bg-black/40 hover:border-white/20 transition cursor-pointer group">
                            <img src="{{ $idBackUrl }}" alt="ID Image (Back)" class="w-full h-auto object-contain max-h-80 group-hover:opacity-90 transition" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-4 text-center text-red-300\'><p>Failed to load image</p><p class=\'text-xs text-slate-400 mt-1\'>Path: {{ $verificationRequest->id_image_back_path }}</p></div>';">
                            <p class="text-xs text-slate-400 py-2 px-3">Click to view full size</p>
                        </button>
                    @else
                        <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-center">
                            <p class="text-red-300 text-sm">Image file not found</p>
                            <p class="text-slate-400 text-xs mt-2 break-all">Path: {{ $verificationRequest->id_image_back_path }}</p>
                            <p class="text-slate-400 text-xs">Expected location: storage/app/public/{{ $verificationRequest->id_image_back_path }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Approve/Reject modal --}}
<div id="decisionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4" onclick="closeDecisionModal(event)">
    <div class="glass p-4 sm:p-6 rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h3 id="decisionModalTitle" class="text-lg font-bold text-white mb-2">Confirm</h3>
        <p id="decisionModalMessage" class="text-slate-400 text-sm mb-4"></p>

        <div id="decisionApproveFields" class="hidden">
            <label class="form-label">Admin Notes (Optional)</label>
            <textarea id="decisionAdminNotes" rows="3" class="form-input w-full" placeholder="Add any notes about this approval..."></textarea>
        </div>

        <div id="decisionRejectFields" class="hidden">
            <label class="form-label">Rejection Reason (Required)</label>
            <textarea id="decisionRejectionReason" rows="3" class="form-input w-full" placeholder="Please provide a reason for rejection..."></textarea>
            <p id="decisionRejectError" class="hidden mt-2 text-sm text-red-300">Rejection reason is required.</p>
        </div>

        <div class="flex gap-2 justify-end mt-5">
            <button type="button" class="btn-secondary" onclick="closeDecisionModal()">Cancel</button>
            <button type="button" id="decisionModalPrimaryBtn" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- Image lightbox -->
<div id="imageLightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/90 p-4" onclick="closeImageLightbox(event)">
    <div class="relative max-w-4xl max-h-[90vh] w-full flex items-center justify-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageLightbox()" class="absolute -top-10 right-0 p-2 text-white hover:text-slate-300 transition" aria-label="Close">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="lightboxImage" src="" alt="" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
        <p id="lightboxCaption" class="absolute -bottom-8 left-0 right-0 text-center text-slate-400 text-sm"></p>
    </div>
</div>

<script>
function openImageLightbox(url, caption) {
    const lb = document.getElementById('imageLightbox');
    const img = document.getElementById('lightboxImage');
    const cap = document.getElementById('lightboxCaption');
    img.src = url;
    img.alt = caption;
    cap.textContent = caption;
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeImageLightbox(e) {
    if (e && e.target !== document.getElementById('imageLightbox')) return;
    const lb = document.getElementById('imageLightbox');
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeImageLightbox();
});
</script>
<script>
let __decisionAction = null;

function openDecisionModal(action) {
    __decisionAction = action;

    const modal = document.getElementById('decisionModal');
    const titleEl = document.getElementById('decisionModalTitle');
    const msgEl = document.getElementById('decisionModalMessage');
    const approveFields = document.getElementById('decisionApproveFields');
    const rejectFields = document.getElementById('decisionRejectFields');
    const btn = document.getElementById('decisionModalPrimaryBtn');
    const rejectErr = document.getElementById('decisionRejectError');

    if (rejectErr) rejectErr.classList.add('hidden');

    if (action === 'approve') {
        titleEl.textContent = 'Approve verification';
        msgEl.textContent = 'Approve this verification request?';
        approveFields?.classList.remove('hidden');
        rejectFields?.classList.add('hidden');
        btn.textContent = 'Approve';
        btn.className = 'px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium';
        document.getElementById('decisionAdminNotes')?.focus();
    } else {
        titleEl.textContent = 'Reject verification';
        msgEl.textContent = 'Reject this verification request? This will mark the request as rejected.';
        rejectFields?.classList.remove('hidden');
        approveFields?.classList.add('hidden');
        btn.textContent = 'Reject';
        btn.className = 'px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium';
        document.getElementById('decisionRejectionReason')?.focus();
    }

    modal?.classList.remove('hidden');
    modal?.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDecisionModal(e) {
    if (e && e.target && e.target !== document.getElementById('decisionModal')) return;
    const modal = document.getElementById('decisionModal');
    modal?.classList.add('hidden');
    modal?.classList.remove('flex');
    document.body.style.overflow = '';
    __decisionAction = null;
}

function submitDecision() {
    if (__decisionAction === 'approve') {
        const notes = document.getElementById('decisionAdminNotes')?.value ?? '';
        const input = document.getElementById('quickApproveNotes');
        if (input) input.value = notes;
        document.getElementById('quickApproveForm')?.submit();
        return;
    }

    if (__decisionAction === 'reject') {
        const reason = (document.getElementById('decisionRejectionReason')?.value ?? '').trim();
        if (!reason) {
            document.getElementById('decisionRejectError')?.classList.remove('hidden');
            document.getElementById('decisionRejectionReason')?.focus();
            return;
        }
        const input = document.getElementById('quickRejectReason');
        if (input) input.value = reason;
        document.getElementById('quickRejectForm')?.submit();
    }
}

document.getElementById('decisionModalPrimaryBtn')?.addEventListener('click', function () {
    submitDecision();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDecisionModal();
});
</script>
@endsection
