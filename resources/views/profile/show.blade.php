@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <h1 class="text-2xl sm:text-3xl font-bold text-white">Profile</h1>
    </div>

    <!-- Profile Information -->
    <div class="glass p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Profile Information</h2>
        <div class="flex items-center gap-6 mb-6">
            <div class="shrink-0">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="Profile photo" class="w-16 h-16 rounded-full object-cover border border-white/20">
                @else
                    <div class="w-16 h-16 rounded-full bg-blue-500/20 border border-blue-500/40 flex items-center justify-center">
                        <span class="text-lg font-semibold text-blue-200">{{ mb_substr($user->name, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div class="text-sm text-slate-300">
                <p class="font-medium">{{ $user->name }}</p>
                <p class="text-slate-400">{{ $user->email }}</p>
                @if(($user->role ?? null) === 'driver')
                    <p class="text-slate-400 mt-1">Phone: {{ $user->phone ?: 'Not set' }}</p>
                @endif
                <p class="mt-1 text-slate-400">Upload a square image for best results. Max 5MB.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5 max-w-2xl">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                </div>
                @if(($user->role ?? null) === 'driver')
                    <div>
                        <label class="form-label">Phone Number (Optional)</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="09XXXXXXXXX">
                        <p class="text-xs text-slate-400 mt-1.5">For security and emergency contact purposes.</p>
                    </div>
                @endif
            </div>
            <div>
                <label class="form-label">Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*" class="form-input">
                <p class="text-xs text-slate-400 mt-1.5">JPG, PNG or GIF. Max 5MB.</p>
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

