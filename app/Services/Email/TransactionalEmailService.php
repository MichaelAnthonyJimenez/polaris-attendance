<?php

namespace App\Services\Email;

class TransactionalEmailService
{
    public function __construct(
        private readonly PhpMailerService $smtpSender,
        private readonly BrevoTransactionalEmailService $brevoSender,
    ) {}

    /**
     * @param array<int, array{email:string,name?:string|null}> $to
     */
    public function send(array $to, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        $brevoKey = (string) config('services.brevo.api_key');

        if ($brevoKey !== '') {
            try {
                $this->brevoSender->send($to, $subject, $htmlBody, $textBody);
                return;
            } catch (\Throwable $e) {
                report($e);
                // fall back to SMTP below
            }
        }

        $this->smtpSender->send($to, $subject, $htmlBody, $textBody);
    }

    public function sendTo(string $email, string $subject, string $htmlBody, ?string $textBody = null, ?string $name = null): void
    {
        $this->send([['email' => $email, 'name' => $name]], $subject, $htmlBody, $textBody);
    }
}

