<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\Email\TransactionalEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function show(Request $request): View
    {
        return view('auth.two-factor');
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = (int) $request->session()->get('two_factor_user_id', 0);
        if ($userId <= 0) {
            return redirect()->route('login')->withErrors(['email' => 'Your session expired. Please log in again.']);
        }

        $hash = Cache::get('two_factor_code:' . $userId);
        if (!$hash || !Hash::check($data['code'], $hash)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        Cache::forget('two_factor_code:' . $userId);
        $request->session()->forget('two_factor_user_id');

        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        $user = User::find($userId);
        if ($user) {
            $firstOtpCompletion = !$user->otp_verified_at;

            $user->forceFill(['otp_verified_at' => now()])->save();

            // Login code sets Users "Verified" for non-drivers only. Drivers get that after admin approves facial/ID verification.
            if (($user->role ?? '') !== 'driver' && !$user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            // Send welcome after the first successful OTP (same moment we used to set email_verified_at for new accounts).
            if ($firstOtpCompletion && $user->role !== 'admin') {
                try {
                    app(TransactionalEmailService::class)->sendTo(
                        $user->email,
                        'Welcome to Polaris Attendance',
                        view('emails.welcome-new-user', ['user' => $user])->render(),
                        null,
                        $user->name
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return redirect()->intended('/dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = (int) $request->session()->get('two_factor_user_id', 0);
        if ($userId <= 0) {
            return redirect()->route('login')->withErrors(['email' => 'Your session expired. Please log in again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Unable to resend code. Please log in again.']);
        }

        $code = (string) random_int(100000, 999999);

        Cache::put(
            'two_factor_code:' . $user->id,
            Hash::make($code),
            now()->addMinutes((int) Setting::get('two_factor_expire_minutes', 10))
        );

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

        return back()->with('status', 'A new code has been sent to your email.');
    }
}

