<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Setting;
use App\Models\User;
use App\Services\Email\TransactionalEmailService;
use App\Services\Security\RecaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $twoFactorEnabled = (bool) Setting::get('two_factor_enabled', Setting::get('enable_two_factor', false));

            // Newly registered non-admin users must verify with an OTP on first login.
            $firstTimeUser = $user && !$user->email_verified_at && $user->role !== 'admin';

            // Force email code on first login after registration, even if global 2FA is off.
            if (($twoFactorEnabled || $firstTimeUser) && $user) {
                $code = (string) random_int(100000, 999999);

                Cache::put(
                    'two_factor_code:' . $user->id,
                    Hash::make($code),
                    now()->addMinutes((int) Setting::get('two_factor_expire_minutes', 10))
                );

                $request->session()->put('two_factor_user_id', $user->id);

                Auth::logout();

                try {
                    app(TransactionalEmailService::class)->sendTo(
                        $user->email,
                        'Your Polaris Attendance verification code',
                        view('emails.two-factor-code', ['user' => $user, 'code' => $code])->render(),
                        null,
                        $user->name
                    );
                } catch (\Throwable $e) {
                    report($e);
                }

                return redirect()->route('two-factor.show');
            }

            AuditLogger::log('login', null, null, null, null, 'User logged in');

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];

        if (config('services.recaptcha.site_key')) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $data = $request->validate($rules, [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA to continue.',
            'terms.accepted' => 'You must accept the Terms of Service and Privacy Policy.',
        ]);

        if (config('services.recaptcha.site_key')) {
            $ok = app(RecaptchaService::class)->verify(
                $request->input('g-recaptcha-response'),
                $request->ip()
            );

            if (!$ok) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
                ]);
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(10),
        ]);

        AuditLogger::log('register', 'User', $user->id, null, ['name' => $user->name, 'email' => $user->email], 'New user registered');

        return redirect()->route('register.complete');
    }

    public function showRegisterComplete(): View
    {
        return view('auth.register-complete');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        AuditLogger::log('logout', null, null, null, null, 'User logged out');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

