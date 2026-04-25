<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Setting;
use App\Models\User;
use App\Services\Email\TransactionalEmailService;
use App\Services\Security\TurnstileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
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

        $turnstileEnabled = (bool) config('services.turnstile.login_enabled', true)
            && (string) config('services.turnstile.site_key') !== '';

        if ($turnstileEnabled) {
            $rules['cf-turnstile-response'] = ['required', 'string'];
        }

        $credentials = $request->validate($rules, [
            'cf-turnstile-response.required' => 'Please complete the captcha challenge.',
        ]);

        if ($turnstileEnabled) {
            $ok = app(TurnstileService::class)->verify(
                $request->input('cf-turnstile-response'),
                $request->ip()
            );

            if (! $ok) {
                throw ValidationException::withMessages([
                    'cf-turnstile-response' => 'Captcha verification failed. Please try again.',
                ]);
            }
        }

        if (Auth::attempt($request->only(['email', 'password']), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user && $this->shouldRequireOtp($user)) {
                return $this->beginOtpChallenge($request, $user);
            }

            AuditLogger::log('login', null, null, null, null, 'User logged in');

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if (! app()->bound('Laravel\\Socialite\\Contracts\\Factory')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is unavailable because Socialite is not installed.',
            ]);
        }

        $googleClientId = (string) config('services.google.client_id');
        $googleClientSecret = (string) config('services.google.client_secret');
        $googleRedirect = (string) config('services.google.redirect');

        if ($googleClientId === '' || $googleClientSecret === '' || $googleRedirect === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is not configured yet. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI (or APP_URL) in the environment.',
            ]);
        }

        $request->session()->put('google_login_remember', $request->boolean('remember'));

        $socialite = app('Laravel\\Socialite\\Contracts\\Factory');

        $driver = $socialite->driver('google');

        // On platforms like Railway (HTTPS behind proxy), explicitly bind the callback
        // URL to the current request host/scheme to avoid redirect_uri mismatches.
        $callbackUrl = url('/auth/google/callback');
        if (method_exists($driver, 'redirectUrl')) {
            $driver->redirectUrl($callbackUrl);
        }

        return $driver->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            if (! app()->bound('Laravel\\Socialite\\Contracts\\Factory')) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Google sign-in is unavailable because Socialite is not installed.',
                ]);
            }
            $socialite = app('Laravel\\Socialite\\Contracts\\Factory');
            $driver = $socialite->driver('google');
            if (method_exists($driver, 'redirectUrl')) {
                $driver->redirectUrl(url('/auth/google/callback'));
            }
            try {
                $googleUser = $driver->user();
            } catch (\InvalidArgumentException $stateException) {
                // Railway / proxy deployments can occasionally lose OAuth state.
                if (method_exists($driver, 'stateless')) {
                    $googleUser = $driver->stateless()->user();
                } else {
                    throw $stateException;
                }
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'email' => 'Google authentication failed. Please try again.',
            ]);
        }

        $email = trim((string) $googleUser->getEmail());
        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Google account email is required.',
            ]);
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;
        $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User'));

        if ($isNew) {
            $user->name = $name;
            $user->password = Hash::make(Str::random(40));
            $user->role = 'driver';
        } else {
            if ($user->name === '' && $name !== '') {
                $user->name = $name;
            }
        }
        try {
            $user->save();
        } catch (QueryException $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'email' => 'Google account verified, but saving your user failed. Please contact support and run latest migrations on production.',
            ]);
        }

        $remember = (bool) $request->session()->pull('google_login_remember', false);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($this->shouldRequireOtp($user)) {
            return $this->beginOtpChallenge($request, $user);
        }

        AuditLogger::log('login', null, null, null, null, 'User logged in via Google');

        return redirect()->intended('/dashboard');
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

        $data = $request->validate($rules, [
            'terms.accepted' => 'You must accept the Terms of Service and Privacy Policy.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'driver',
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

    private function shouldRequireOtp(User $user): bool
    {
        $role = mb_strtolower(trim((string) ($user->role ?? '')));
        if ($role === 'driver') {
            return (bool) Setting::get('driver_two_factor_enabled', false);
        }

        return (bool) Setting::get('two_factor_enabled', Setting::get('enable_two_factor', false));
    }

    private function beginOtpChallenge(Request $request, User $user): RedirectResponse
    {
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
}

