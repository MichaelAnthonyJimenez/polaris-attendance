@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-white">Profile</h1>
    </div>

    <!-- Profile Information -->
    <div class="glass p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Profile Information</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5 max-w-2xl">
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
            <button type="submit" class="btn-primary">Update Profile</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="glass p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Change Password</h2>
        <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-5 max-w-2xl">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" required class="form-input">
            </div>
            <div>
                <label class="form-label">New Password</label>
                <input type="password" name="password" required class="form-input" minlength="8">
            </div>
            <div>
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" required class="form-input" minlength="8">
            </div>
            <button type="submit" class="btn-primary">Update Password</button>
        </form>
    </div>
</div>
@endsection

