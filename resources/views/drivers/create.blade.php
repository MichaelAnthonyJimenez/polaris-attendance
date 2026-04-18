@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-6">Add Driver</h1>
        <form method="POST" action="{{ route('drivers.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Badge Number</label>
                    <input type="text" name="badge_number" value="{{ old('badge_number') }}" class="form-input" placeholder="Next number if left blank">
                    <p class="text-xs text-slate-400 mt-1.5">Leave blank to assign the next sequential badge.</p>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="driver@example.com">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Vehicle Number</label>
                    <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Profile Photo (Optional)</label>
                <input type="file" name="profile_photo" accept="image/*" class="form-input">
                <p class="text-xs text-slate-400 mt-1.5">Shown in driver details. Max 5MB.</p>
            </div>
            <div>
                <label class="form-label">Face Photo (Optional)</label>
                <input type="file" name="face_image" accept="image/*" class="form-input">
                <p class="text-xs text-slate-400 mt-1.5">Used to enroll for facial recognition; max 5MB.</p>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="active" id="active" value="1" checked class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/50">
                <label for="active" class="ml-2 text-sm text-slate-200">Active</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Driver</button>
                <a href="{{ route('drivers.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

