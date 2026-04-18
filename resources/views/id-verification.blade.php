@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8 rounded-xl">
        <h1 class="text-2xl font-bold text-white mb-6">ID Verification</h1>
        <form method="POST" action="{{ route('driver-verification.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            {{-- this submission includes both an ID image and a selfie, so use the matching enum value --}}
            <input type="hidden" name="verification_method" value="id_with_selfie">

            <div class="space-y-4">
                <div>
                    <label class="form-label">ID Image</label>
                    <input type="file" name="id_image" accept="image/*" required class="form-input">
                    <p class="text-xs text-slate-400 mt-1">Upload a clear photo of your ID document</p>
                </div>

                <div>
                    <label class="form-label">Selfie with ID</label>
                    <input type="file" name="selfie_with_id" accept="image/*" required class="form-input">
                    <p class="text-xs text-slate-400 mt-1">Take a selfie while holding your ID</p>
                </div>

                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" name="manual[name]" placeholder="Full name" class="form-input">
                </div>

                <div>
                    <label class="form-label">License / ID Number</label>
                    <input type="text" name="manual[license_number]" placeholder="License / ID number" class="form-input">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-primary">Submit Verification</button>
                <a href="{{ route('verification.popup') }}" class="btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
