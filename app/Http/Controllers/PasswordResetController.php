<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\Email\TransactionalEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $email = Str::lower(trim($request->input('email', '')));

        $request->merge(['email' => $email])->validate([
            'email' => ['required', 'email'],
        ]);

        $enabled = (bool) Setting::get('enable_password_reset', true);
        if (!$enabled) {
            return back()->withErrors(['email' => 'Password reset is currently disabled.']);
        }

        $user = User::where('email', $email)->first();

        // Always respond with success to avoid account enumeration
        if (!$user) {
            return back()->with('status', 'If your email exists in our system, we will send a reset link.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));

        try {
            app(TransactionalEmailService::class)->sendTo(
                $user->email,
                'Reset your Polaris Attendance password',
                view('emails.password-reset', ['user' => $user, 'resetUrl' => $resetUrl])->render(),
                null,
                $user->name
            );
        } catch (\Throwable $e) {
            report($e);
            if ((bool) config('app.debug')) {
                return back()->withErrors(['email' => 'Unable to send reset email. Please check your mail settings and try again.']);
            }
        }

        return back()->with('status', 'If your email exists in our system, we will send a reset link.');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $email = Str::lower(trim($request->input('email', '')));

        $data = $request->merge(['email' => $email])->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$record || !Hash::check($data['token'], $record->token)) {
            return back()->withErrors(['email' => 'This password reset link is invalid.']);
        }

        $expireMinutes = (int) config('auth.passwords.users.expire', 60);
        $createdAt = $record?->created_at ? Carbon::parse($record->created_at) : null;
        if ($createdAt && now()->diffInMinutes($createdAt) > $expireMinutes) {
            return back()->withErrors(['email' => 'This password reset link has expired.']);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Unable to reset password for this email.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('status', 'Password reset successfully. You can now log in.');
    }
}

