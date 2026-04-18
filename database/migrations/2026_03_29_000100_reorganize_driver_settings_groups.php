<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['auto_capture_photo', 'auto_submit_camera'] as $key) {
            Setting::where('key', $key)->update(['group' => 'driver_camera']);
        }

        Setting::where('key', 'show_notifications')->where('group', 'driver_preferences')->update(['group' => 'driver_notifications']);

        $soundFallback = Setting::where('key', 'notification_sound')->value('value') ?? '1';
        Setting::firstOrCreate(
            ['key' => 'driver_notification_sound'],
            [
                'value' => $soundFallback,
                'type' => 'boolean',
                'group' => 'driver_notifications',
                'description' => 'Play a sound with reminders and attendance notifications',
            ]
        );

        foreach ([
            ['driver_email_on_checkin', '1', 'Send email when you check in'],
            ['driver_email_on_checkout', '1', 'Send email when you check out'],
            ['driver_browser_notify_checkin', '1', 'Browser notification when you check in'],
            ['driver_browser_notify_checkout', '1', 'Browser notification when you check out'],
            ['driver_reminders_enabled', '1', 'Enable scheduled check-in / check-out reminders'],
        ] as [$key, $value, $desc]) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'boolean',
                    'group' => $key === 'driver_reminders_enabled' ? 'driver_reminders' : 'driver_notifications',
                    'description' => $desc,
                ]
            );
        }
    }

    public function down(): void
    {
        foreach (['auto_capture_photo', 'auto_submit_camera'] as $key) {
            Setting::where('key', $key)->update(['group' => 'driver_attendance']);
        }

        Setting::where('key', 'show_notifications')->update(['group' => 'driver_preferences']);

        foreach (['driver_notification_sound', 'driver_email_on_checkin', 'driver_email_on_checkout', 'driver_browser_notify_checkin', 'driver_browser_notify_checkout', 'driver_reminders_enabled'] as $key) {
            Setting::where('key', $key)->delete();
        }
    }
};
