<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = (string) config('services.recaptcha.secret_key');

        if ($secret === '' || $token === null || $token === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

        if (!$response->ok()) {
            return false;
        }

        return (bool) ($response->json('success') ?? false);
    }
}

