<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureDriverVerified;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureDriverVerified::class);

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        $this->artisan('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
    }

    public function test_admin_sees_curated_settings_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSeeText('Attendance');
        $response->assertSeeText('Attendance Reminder Enabled');
        $response->assertSeeText('General');
        $response->assertSeeText('Enable Activity Logging');
        $response->assertDontSeeText('Sms Notifications Enabled');
        $response->assertDontSeeText('Driver Sms Notifications');
    }

    public function test_driver_sees_driver_setting_groups_only(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);

        $response = $this->actingAs($driver)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSeeText('Camera');
        $response->assertSeeText('Auto Capture Photo');
        $response->assertSeeText('Reminders');
        $response->assertDontSeeText('General');
        $response->assertDontSeeText('Email From Address');
        $response->assertDontSeeText('Smtp Enabled');
    }

    public function test_admin_can_update_admin_setting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'settings' => [
                'smtp_enabled' => '1',
            ],
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertSame('1', (string) Setting::query()->where('key', 'smtp_enabled')->value('value'));
    }

    public function test_driver_cannot_update_admin_setting_but_can_update_driver_setting(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);

        $originalAdminValue = (string) Setting::query()->where('key', 'smtp_enabled')->value('value');

        $response = $this->actingAs($driver)->put(route('settings.update'), [
            'settings' => [
                'smtp_enabled' => '1',
                'auto_capture_photo' => '1',
            ],
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertSame($originalAdminValue, (string) Setting::query()->where('key', 'smtp_enabled')->value('value'));
        $this->assertSame('1', (string) Setting::query()->where('key', 'auto_capture_photo')->value('value'));
    }
}

