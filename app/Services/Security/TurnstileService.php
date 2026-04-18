<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;

class TurnstileService
{
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = (string) config('services.turnstile.secret_key');

        if ($secret === '' || $token === null || $token === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

        if (! $response->ok()) {
            return false;
        }

        return (bool) ($response->json('success') ?? false);
    }
}
