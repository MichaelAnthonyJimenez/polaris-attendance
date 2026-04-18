@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-3 text-center">Reset Password</h1>
        <p class="text-sm text-slate-300 text-center mb-6">
            Choose a new password for your account.
        </p>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-300 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required class="form-input">
            </div>

            <div>
                <label class="form-label">New Password</label>
                <div class="relative">
                    <input type="password" name="password" id="reset-password" required class="form-input pr-10">
                    <button type="button"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-300 hover:text-white"
                            onclick="togglePasswordVisibility('reset-password', this)">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path class="hidden toggle-eye-slash" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="form-label">Confirm New Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="reset-password-confirmation" required class="form-input pr-10">
                    <button type="button"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-300 hover:text-white"
                            onclick="togglePasswordVisibility('reset-password-confirmation', this)">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path class="hidden toggle-eye-slash" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full">Reset password</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, buttonEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        buttonEl.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        const slash = buttonEl.querySelector('.toggle-eye-slash');
        if (slash) {
            slash.classList.toggle('hidden', !isPassword);
        }
    }
</script>
@endpush
