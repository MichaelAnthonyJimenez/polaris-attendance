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
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];

        if (config('services.recaptcha.login_enabled')) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $data = $request->validate($rules, [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA to continue.',
        ]);

        if (config('services.recaptcha.login_enabled')) {
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

        $normalizedEmail = Str::lower(trim($data['email']));

        $credentials = [
            'email' => $normalizedEmail,
            'password' => $data['password'],
        ];

        $remember = $request->boolean('remember');
        $authenticated = Auth::attempt($credentials, $remember);

        // Case-sensitive DB collations: attempt uses exact email match; normalize casing when LOWER matches.
        if (!$authenticated) {
            $resolved = User::query()
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->first();

            if ($resolved && $resolved->email !== $normalizedEmail) {
                $authenticated = Auth::attempt(
                    [
                        'email' => $resolved->email,
                        'password' => $data['password'],
                    ],
                    $remember
                );

                if ($authenticated) {
                    $resolved->forceFill(['email' => $normalizedEmail])->save();
                }
            }
        }

        if ($authenticated) {
            $request->session()->regenerate();

            $user = Auth::user();
            $twoFactorEnabled = (bool) Setting::get('two_factor_enabled', Setting::get('enable_two_factor', false));

            // Newly registered non-admin users must verify with an OTP on first login (tracked separately from admin driver verification).
            $firstTimeUser = $user && !$user->otp_verified_at && $user->role !== 'admin';

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

        // Fallback for legacy data inconsistencies (plain-text passwords from older setups).
        $legacyUser = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if ($legacyUser && hash_equals($legacyUser->password, $data['password'])) {
            $legacyUser->forceFill([
                'email' => $normalizedEmail,
                'password' => Hash::make($data['password']),
            ])->save();

            Auth::login($legacyUser, $remember);
            $request->session()->regenerate();

            AuditLogger::log('login', null, null, null, null, 'User logged in via legacy credential fallback');

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput(['email' => $normalizedEmail]);
    }

    public function showRegister(): View
    {
        abort_unless((bool) Setting::get('enable_registration', true), 404);

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        abort_unless((bool) Setting::get('enable_registration', true), 404);

        $email = Str::lower(trim($request->input('email', '')));

        $data = $request->merge(['email' => $email])->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'You must accept the Terms of Service and Privacy Policy.',
        ]);

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

