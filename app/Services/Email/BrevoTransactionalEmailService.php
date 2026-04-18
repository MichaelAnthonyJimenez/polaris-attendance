<?php

namespace App\Services\Email;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BrevoTransactionalEmailService
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    /**
     * @param array<int, array{email:string,name?:string|null}> $to
     */
    public function send(array $to, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        $apiKey = (string) config('services.brevo.api_key');
        if ($apiKey === '') {
            throw new \RuntimeException('Brevo API key is not configured.');
        }

        $fromEmail = (string) config('mail.from.address', env('MAIL_FROM_ADDRESS', 'noreply@polaris.local'));
        $fromName = (string) config('mail.from.name', env('MAIL_FROM_NAME', 'Polaris Attendance'));

        $payload = [
            'sender' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'to' => array_map(
                static fn (array $r) => array_filter([
                    'email' => $r['email'],
                    'name' => $r['name'] ?? null,
                ], static fn ($v) => $v !== null && $v !== ''),
                $to
            ),
            'subject' => $subject,
            'htmlContent' => $htmlBody,
            'textContent' => $textBody ?? trim(strip_tags($htmlBody)),
        ];

        try {
            $res = Http::timeout(10)
                ->withHeaders([
                    'api-key' => $apiKey,
                    'accept' => 'application/json',
                ])
                ->post(self::ENDPOINT, $payload);
        } catch (ConnectionException $e) {
            throw $e;
        }

        if (!$res->successful()) {
            $details = $res->json();
            throw new \RuntimeException(
                'Brevo email send failed (HTTP ' . $res->status() . '): ' . json_encode($details ?: $res->body())
            );
        }
    }

    public function sendTo(string $email, string $subject, string $htmlBody, ?string $textBody = null, ?string $name = null): void
    {
        $this->send([['email' => $email, 'name' => $name]], $subject, $htmlBody, $textBody);
    }
}

