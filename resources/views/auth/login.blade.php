@extends('layouts.app')

@section('content')
<div class="flex w-full min-h-[calc(100svh-13rem)] flex-col justify-center sm:min-h-0">
    <div class="mx-auto w-full max-w-md sm:max-w-lg lg:max-w-2xl">
        <div class="glass p-8">
            <h1 class="text-2xl font-bold text-white mb-6 text-center">Login</h1>
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label flex items-center justify-between">
                        <span>Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="login-password" required class="form-input pr-10">
                        <button type="button"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-300 hover:text-white"
                                onclick="togglePasswordVisibility('login-password', this)">
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
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/50">
                    <label for="remember" class="ml-2 text-sm text-slate-200">Remember me</label>
                </div>
                @if (config('services.recaptcha.login_enabled'))
                    <div class="mt-2">
                        <div class="flex justify-center overflow-x-auto">
                            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                        </div>
                        @error('g-recaptcha-response')
                            <p class="mt-2 text-sm text-red-400 text-center">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <button type="submit" class="btn-primary w-full">Login</button>
                <div class="text-sm text-center">
                    <a href="{{ route('password.request') }}" class="text-blue-400 hover:text-blue-300 font-medium">Forgot password?</a>
                </div>
            </form>
            <p class="text-sm mt-6 text-center text-slate-300">
                Need an account?
                <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-medium">Get Started</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('head-scripts')
    @if (config('services.recaptcha.login_enabled'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
@endpush

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
