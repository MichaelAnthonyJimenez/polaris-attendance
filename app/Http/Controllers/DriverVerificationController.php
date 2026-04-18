<?php

namespace App\Http\Controllers;

use App\Models\DriverVerification;
use App\Services\Email\TransactionalEmailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverVerificationController extends Controller
{
    public function index(): View
    {
        $verifications = DriverVerification::with(['user', 'driver', 'reviewer'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('driver-verification.index', compact('verifications'));
    }

    public function show(DriverVerification $verification): View
    {
        $verificationRequest = $verification->load(['user', 'driver', 'reviewer']);

        return view('driver-verification.show', compact('verificationRequest'));
    }

    public function approve(Request $request, DriverVerification $verification): RedirectResponse
    {
        $verification->fill([
            'status' => 'approved',
            'admin_notes' => $request->input('admin_notes', $verification->admin_notes),
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ])->save();

        $this->markDriverAccountVerified($verification);

        return redirect()
            ->route('driver-verification.show', $verification)
            ->with('success', 'Verification approved.');
    }

    public function reject(Request $request, DriverVerification $verification): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $verification->fill([
            'status' => 'rejected',
            'reason' => $data['rejection_reason'],
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ])->save();

        return redirect()
            ->route('driver-verification.show', $verification)
            ->with('success', 'Verification rejected.');
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return redirect()
                ->route('driver-verification.index')
                ->with('error', 'No verification requests selected.');
        }

        $verifications = DriverVerification::whereIn('id', $ids)->get();

        foreach ($verifications as $verification) {
            $verification->fill([
                'status' => 'approved',
                'reviewer_id' => Auth::id(),
                'reviewed_at' => now(),
            ])->save();

            $this->markDriverAccountVerified($verification);
        }

        return redirect()
            ->route('driver-verification.index')
            ->with('success', 'Selected verification requests have been approved.');
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DriverVerification::whereIn('id', $data['ids'])->update([
            'status' => 'rejected',
            'reason' => $data['reason'],
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('driver-verification.index')
            ->with('success', 'Selected verification requests have been rejected.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return redirect()
                ->route('driver-verification.index')
                ->with('error', 'No verification requests selected.');
        }

        DriverVerification::whereIn('id', $ids)->delete();

        return redirect()
            ->route('driver-verification.index')
            ->with('success', 'Selected verification requests have been deleted.');
    }

    /**
     * Drivers only show as "Verified" on the Users list after admin approval (email_verified_at), not after the login OTP.
     */
    private function markDriverAccountVerified(DriverVerification $verification): void
    {
        $user = $verification->user;

        if (! $user || ($user->role ?? '') !== 'driver') {
            return;
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();

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
}

