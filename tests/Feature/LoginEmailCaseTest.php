<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginEmailCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_accepts_email_when_only_casing_differs_from_stored_value(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        config([
            'services.recaptcha.site_key' => '',
            'services.recaptcha.secret_key' => '',
            'services.recaptcha.login_enabled' => false,
        ]);

        User::factory()->create([
            'email' => 'CapsMail@Test.local',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'capsmail@test.local',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertSame(
            'capsmail@test.local',
            User::query()->where('email', 'capsmail@test.local')->value('email')
        );
    }
}
