<?php

namespace App\Mail;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;

class AnnouncementMail extends Mailable
{
    public function __construct(
        public readonly User $user,
        public readonly Announcement $announcement,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Polaris Attendance — Announcement",
            from: config('mail.from.address', 'noreply@polaris.local'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement',
        );
    }
}

