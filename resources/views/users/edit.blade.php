@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">Edit User</h1>
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Role</label>
                <select name="role" required class="form-select">
                    <option value="driver" {{ old('role', $user->role) == 'driver' ? 'selected' : '' }}>Driver</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div>
                <label class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-input" minlength="8">
            </div>
            <div>
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-input" minlength="8">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Update User</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

