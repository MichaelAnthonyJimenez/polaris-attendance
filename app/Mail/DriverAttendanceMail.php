<?php

namespace App\Mail;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverAttendanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Attendance $attendance,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->attendance->type === 'check_out' ? 'Check-out' : 'Check-in';

        return new Envelope(
            subject: "Polaris Attendance — {$label} recorded",
            from: config('mail.from.address', 'noreply@polaris.local'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.driver-attendance',
        );
    }
}
