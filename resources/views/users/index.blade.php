@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white">Users</h1>
        <a href="{{ route('users.create') }}" class="btn-primary w-full sm:w-auto">Add User</a>
    </div>

    <div class="glass p-4 sm:p-6 md:p-7">
        <form action="{{ route('users.index') }}" method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto min-w-0">
                <label for="sort-users" class="text-sm text-slate-300 shrink-0">Sort by</label>
                <select
                    name="sort"
                    id="sort-users"
                    class="w-full sm:w-auto min-w-0 max-w-full px-3 py-2 rounded-lg bg-slate-800/50 border border-slate-600 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    onchange="this.form.submit()"
                >
                    <option value="" {{ empty($sort) ? 'selected' : '' }}>Default</option>
                    <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="name_asc" {{ ($sort ?? '') === 'name_asc' ? 'selected' : '' }}>Name A–Z</option>
                    <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Name Z–A</option>
                    <option value="status_verified" {{ ($sort ?? '') === 'status_verified' ? 'selected' : '' }}>Status (Verified)</option>
                    <option value="status_unverified" {{ ($sort ?? '') === 'status_unverified' ? 'selected' : '' }}>Status (Unverified)</option>
                    <option value="role_admin" {{ ($sort ?? '') === 'role_admin' ? 'selected' : '' }}>Role (Admins)</option>
                    <option value="role_driver" {{ ($sort ?? '') === 'role_driver' ? 'selected' : '' }}>Role (Drivers)</option>
                </select>
            </div>

            <label for="search-users" class="sr-only">Search users</label>
            <input
                type="text"
                name="search"
                id="search-users"
                value="{{ old('search', $search ?? '') }}"
                placeholder="Search by name or email..."
                class="flex-1 min-w-0 sm:min-w-[200px] px-4 py-2 rounded-lg bg-slate-800/50 border border-slate-600 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            >

            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition">
                Search
            </button>

            @if(!empty($search ?? '') || !empty($sort ?? ''))
                <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-lg bg-slate-600 hover:bg-slate-500 text-white font-medium transition">
                    Clear
                </a>
            @endif
        </form>

        <!-- Mobile cards -->
        <div class="space-y-3 md:hidden">
            @forelse($users as $user)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm text-slate-200 font-medium truncate">
                                {{ $user->name }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $user->email }}
                            </div>
                        </div>
                        <div class="flex flex-row sm:flex-col gap-1 sm:shrink-0">
                            <span class="px-2 py-1 rounded text-xs {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-200' : 'bg-blue-500/20 text-blue-200' }}">
                                {{ ucfirst($user->role ?? 'driver') }}
                            </span>
                            <span class="px-2 py-1 rounded text-xs {{ $user->email_verified_at ? 'bg-emerald-500/20 text-emerald-200' : 'bg-slate-700/40 text-slate-200' }}">
                                {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 text-sm text-slate-300">
                        <span class="text-slate-500">Created:</span>
                        <span class="text-slate-200">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    @if(($user->role ?? null) === 'driver')
                        <div class="mt-1 text-sm text-slate-300">
                            <span class="text-slate-500">Badge:</span>
                            <span class="text-slate-200">{{ $user->badge_number ?: '—' }}</span>
                        </div>
                    @endif

                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('users.show', $user) }}" class="text-blue-400 hover:text-blue-300 text-sm">View</a>
                        <a href="{{ route('users.edit', $user) }}" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                        <form
                            action="{{ route('users.destroy', $user) }}"
                            method="POST"
                            class="inline"
                            data-confirm-modal="true"
                            data-confirm-title="Delete account"
                            data-confirm-message="Are you sure you want to delete this account? This action cannot be undone."
                            data-confirm-confirm-text="Delete"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400">No users found.</div>
            @endforelse
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Badge</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="font-medium">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-200 border border-purple-500/40' : 'bg-blue-500/20 text-blue-200 border border-blue-500/40' }}">
                                    {{ ucfirst($user->role ?? 'driver') }}
                                </span>
                            </td>
                            <td>{{ ($user->role ?? null) === 'driver' ? ($user->badge_number ?: '—') : '—' }}</td>
                            <td>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $user->email_verified_at ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-500/40' : 'bg-slate-700/40 text-slate-200 border border-slate-600/60' }}">
                                    {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('users.show', $user) }}" class="text-blue-400 hover:text-blue-300 text-sm">View</a>
                                    <a href="{{ route('users.edit', $user) }}" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                    <form
                                        action="{{ route('users.destroy', $user) }}"
                                        method="POST"
                                        class="inline"
                                        data-confirm-modal="true"
                                        data-confirm-title="Delete account"
                                        data-confirm-message="Are you sure you want to delete this account? This action cannot be undone."
                                        data-confirm-confirm-text="Delete"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="mt-4 overflow-x-auto pb-1">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = Array.from(document.querySelectorAll('form[data-confirm-modal="true"]'));
        if (!forms.length) return;

        let pendingForm = null;
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[130] hidden items-center justify-center bg-black/70 p-4';
        modal.innerHTML = `
            <div class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
                <h3 id="usersDeleteModalTitle" class="text-lg font-semibold text-white">Delete account</h3>
                <p id="usersDeleteModalMessage" class="mt-2 text-sm text-slate-300">Are you sure?</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" id="usersDeleteModalCancel" class="btn-secondary">Cancel</button>
                    <button type="button" id="usersDeleteModalConfirm" class="btn-primary bg-rose-600 hover:bg-rose-500 shadow-rose-500/20">Delete</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        const titleEl = document.getElementById('usersDeleteModalTitle');
        const messageEl = document.getElementById('usersDeleteModalMessage');
        const cancelBtn = document.getElementById('usersDeleteModalCancel');
        const confirmBtn = document.getElementById('usersDeleteModalConfirm');

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingForm = null;
        }

        function openModal(form) {
            pendingForm = form;
            titleEl.textContent = form.getAttribute('data-confirm-title') || 'Confirm action';
            messageEl.textContent = form.getAttribute('data-confirm-message') || 'Are you sure?';
            confirmBtn.textContent = form.getAttribute('data-confirm-confirm-text') || 'Confirm';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        forms.forEach((form) => {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === 'true') {
                    form.dataset.confirmed = 'false';
                    return;
                }
                e.preventDefault();
                openModal(form);
            });
        });

        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
        confirmBtn.addEventListener('click', function () {
            if (!pendingForm) return;
            pendingForm.dataset.confirmed = 'true';
            pendingForm.submit();
            closeModal();
        });
    });
</script>
@endpush

