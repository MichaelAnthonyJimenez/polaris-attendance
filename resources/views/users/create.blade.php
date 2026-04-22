@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">Add User</h1>
        <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Role</label>
                <select id="userRoleSelect" name="role" required class="form-select">
                    <option value="driver" {{ old('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <div class="mt-3 rounded-lg border border-white/10 bg-white/5 p-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Role permissions</p>
                    <ul id="rolePermissionsList" class="mt-2 space-y-1 text-xs text-slate-200"></ul>
                </div>
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" required class="form-input" minlength="8">
            </div>
            <div>
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="form-input" minlength="8">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Create User</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const select = document.getElementById('userRoleSelect');
        const list = document.getElementById('rolePermissionsList');
        if (!select || !list) return;

        const perms = {
            admin: [
                'Manage users, reports, audit logs, and settings',
                'Approve or reject driver verifications',
                'View all attendance records and exports',
            ],
            driver: [
                'Check in and check out using camera',
                'View personal attendance history and map',
                'Manage own profile and driver settings',
            ],
        };

        function render() {
            const role = select.value === 'admin' ? 'admin' : 'driver';
            list.innerHTML = '';
            (perms[role] || []).forEach((text) => {
                const li = document.createElement('li');
                li.textContent = '- ' + text;
                list.appendChild(li);
            });
        }

        select.addEventListener('change', render);
        render();
    })();
</script>
@endpush

