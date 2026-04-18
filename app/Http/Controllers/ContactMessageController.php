<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\Email\TransactionalEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        try {
            $contactMessage = ContactMessage::create($validated);

            try {
                app(TransactionalEmailService::class)->sendTo(
                    $contactMessage->email,
                    'Re: ' . $contactMessage->subject,
                    view('emails.contact-auto-reply', ['name' => $contactMessage->name, 'subject' => $contactMessage->subject])->render(),
                    null,
                    $contactMessage->name
                );
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()->route('contact')
                ->with('success', 'Your message has been sent successfully. Our team will be in contact with you shortly.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Failed to send message. Please try again.');
        }
    }
}
