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
                <select name="role" required class="form-select">
                    <option value="driver" {{ old('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div>
                <label class="form-label">Badge Number</label>
                <input type="text" name="badge_number" value="{{ old('badge_number') }}" class="form-input" placeholder="Leave blank to assign the next number automatically">
                <p class="text-xs text-slate-400 mt-1.5">Drivers only: leave blank for the next sequential badge, or enter a custom value.</p>
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

