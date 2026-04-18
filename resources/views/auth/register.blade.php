@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-md">
    <div class="glass p-8">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">Register</h1>
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
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
                <label class="form-label flex items-center justify-between">
                    <span>Password</span>
                    <span class="text-xs text-slate-300">At least 8 characters</span>
                </label>
                <div class="relative">
                    <input type="password" name="password" id="register-password" required class="form-input pr-10">
                    <button type="button"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-300 hover:text-white"
                            onclick="togglePasswordVisibility('register-password', this)">
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
                <div class="mt-2 flex items-center justify-between text-xs">
                    <span id="password-strength-text" class="text-slate-300">Strength: —</span>
                    <span id="password-length-text" class="text-slate-300">0 / 8</span>
                </div>
                <div class="mt-2 h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                    <div id="password-strength-bar" class="h-full w-0 rounded-full bg-red-400 transition-all duration-200"></div>
                </div>
            </div>
            <div>
                <label class="form-label">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="register-password-confirmation" required class="form-input pr-10">
                    <button type="button"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-300 hover:text-white"
                            onclick="togglePasswordVisibility('register-password-confirmation', this)">
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

            <div class="flex items-start space-x-2 text-sm text-slate-300">
                <input id="terms" name="terms" type="checkbox" required
                       class="mt-1 w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/50">
                <label for="terms" class="leading-snug">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 text-emerald-400 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7" />
                        </svg>
                        By registering an account, I have read and agreed to the
                    </span>
                    <a href="{{ route('terms-of-service') }}" class="text-blue-400 hover:text-blue-300">Terms of Service</a>
                    and
                    <a href="{{ route('privacy-policy') }}" class="text-blue-400 hover:text-blue-300">Privacy Policy</a>.
                </label>
            </div>

            <button type="submit" class="btn-primary w-full mt-2">Create account</button>
        </form>
        <p class="text-sm mt-6 text-center text-slate-300">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium">Login</a>
        </p>
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

    function scorePassword(pw) {
        if (!pw) return 0;
        let score = 0;
        const length = pw.length;

        // length (max 40)
        score += Math.min(40, length * 5);

        // variety (max 60)
        const hasLower = /[a-z]/.test(pw);
        const hasUpper = /[A-Z]/.test(pw);
        const hasNumber = /[0-9]/.test(pw);
        const hasSymbol = /[^A-Za-z0-9]/.test(pw);
        const varietyCount = [hasLower, hasUpper, hasNumber, hasSymbol].filter(Boolean).length;
        score += varietyCount * 15;

        return Math.min(100, score);
    }

    function updatePasswordStrength() {
        const input = document.getElementById('register-password');
        const text = document.getElementById('password-strength-text');
        const lenText = document.getElementById('password-length-text');
        const bar = document.getElementById('password-strength-bar');
        if (!input || !text || !lenText || !bar) return;

        const pw = input.value || '';
        const score = scorePassword(pw);

        lenText.textContent = `${pw.length} / 8`;

        let label = '—';
        let colorClass = 'text-slate-300';
        let barClass = 'bg-red-400';

        if (pw.length === 0) {
            label = '—';
            bar.style.width = '0%';
        } else if (pw.length < 8 || score < 45) {
            label = 'Weak';
            colorClass = 'text-red-300';
            barClass = 'bg-red-400';
        } else if (score < 75) {
            label = 'Medium';
            colorClass = 'text-amber-300';
            barClass = 'bg-amber-400';
        } else {
            label = 'Strong';
            colorClass = 'text-emerald-300';
            barClass = 'bg-emerald-400';
        }

        text.textContent = `Strength: ${label}`;
        text.className = `text-xs ${colorClass}`;
        lenText.className = `text-xs ${pw.length < 8 ? 'text-red-300' : 'text-slate-300'}`;
        bar.className = `h-full rounded-full ${barClass} transition-all duration-200`;
        bar.style.width = `${Math.max(8, score)}%`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('register-password');
        if (!input) return;
        input.addEventListener('input', updatePasswordStrength);
        updatePasswordStrength();
    });
</script>
@endpush
