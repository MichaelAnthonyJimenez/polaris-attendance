@extends('layouts.app')

@section('content')
<div class="flex w-full min-h-[calc(100svh-12.5rem)] flex-col justify-center py-1 sm:py-2 sm:min-h-0">
    <div class="mx-auto w-full max-w-md">
        <div class="glass p-6 sm:p-8 shadow-2xl shadow-slate-950/40">
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
                                aria-label="Show or hide password"
                                onclick="togglePasswordVisibility('login-password', this)">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
                @if ((bool) config('services.turnstile.login_enabled', true) && config('services.turnstile.site_key'))
                    <div class="mt-2">
                        <div class="flex justify-center overflow-x-auto">
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                        </div>
                        @error('cf-turnstile-response')
                            <p class="mt-2 text-sm text-red-400 text-center">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <button type="submit" class="btn-primary w-full">Login</button>
                <div class="relative py-1">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-white/10"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-3 text-xs text-slate-400 bg-slate-950/40 rounded-full">or continue with</span>
                    </div>
                </div>
                <a
                    href="{{ route('auth.google.redirect') }}"
                    class="w-full inline-flex items-center justify-center gap-3 rounded-xl bg-white text-slate-900 hover:bg-white/95 transition px-4 py-3 text-sm font-semibold border border-white/10"
                >
                    <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.773 32.659 29.18 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.963 3.037l5.657-5.657C34.047 6.053 29.284 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 16.108 18.961 12 24 12c3.059 0 5.842 1.154 7.963 3.037l5.657-5.657C34.047 6.053 29.284 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.079 0 9.767-1.95 13.303-5.121l-6.143-5.2C29.163 35.091 26.691 36 24 36c-5.159 0-9.743-3.318-11.284-7.946l-6.52 5.025C9.506 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.734 2.001-2.119 3.68-4.043 4.879l.003-.002 6.143 5.2C36.95 39.1 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    <span>Google</span>
                </a>
                <p class="text-xs text-center text-slate-400 mt-2">
                    Sign in to polaris-attendance.up.railway.app - Google will allow to access info about you
                </p>
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
    @if ((bool) config('services.turnstile.login_enabled', true) && config('services.turnstile.site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
