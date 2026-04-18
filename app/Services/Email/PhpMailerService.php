<?php

namespace App\Services\Email;

use PHPMailer\PHPMailer\Exception as PhpMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;

class PhpMailerService
{
    /**
     * @param array<int, array{email:string,name?:string|null}> $to
     */
    public function send(array $to, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        $mailer = strtolower(trim((string) env('MAIL_MAILER', 'smtp')));
        if ($mailer === 'log') {
            Log::info('Transactional email (log mailer)', [
                'to' => $to,
                'subject' => $subject,
                'html' => $htmlBody,
                'text' => $textBody ?? strip_tags($htmlBody),
            ]);

            return;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = (string) env('MAIL_HOST', 'smtp.gmail.com');
            $mail->Port = (int) env('MAIL_PORT', 587);
            $username = trim((string) env('MAIL_USERNAME', ''));
            $password = (string) env('MAIL_PASSWORD', '');
            $mail->SMTPAuth = $username !== '';
            $mail->Username = $username;
            $mail->Password = $password;

            $encryption = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));
            if ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            $fromEmail = (string) env('MAIL_FROM_ADDRESS', 'noreply@polaris.local');
            $fromName = (string) env('MAIL_FROM_NAME', 'Polaris Attendance');
            $mail->setFrom($fromEmail, $fromName);

            foreach ($to as $recipient) {
                $mail->addAddress($recipient['email'], $recipient['name'] ?? '');
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?? strip_tags($htmlBody);

            $mail->send();
        } catch (PhpMailerException $e) {
            throw $e;
        }
    }

    public function sendTo(string $email, string $subject, string $htmlBody, ?string $textBody = null, ?string $name = null): void
    {
        $this->send([['email' => $email, 'name' => $name]], $subject, $htmlBody, $textBody);
    }
}

