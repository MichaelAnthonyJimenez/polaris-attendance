<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /** @var list<string> */
    private const ADMIN_GROUP_ORDER = [
        'admin_attendance',
        'admin_backup',
        'admin_driver_management',
        'admin_email',
        'admin_export',
        'admin_face_recognition',
        'admin_location',
        'admin_notifications',
        'admin_performance',
        'admin_reports',
        'admin_security',
        'admin_system',
        'driver_accessibility',
        'driver_dashboard',
        'driver_data_usage',
        'driver_notifications',
        'driver_profile',
        'driver_reminders',
        'driver_security',
        'general',
    ];

    /** @var array<string, list<string>> */
    private const ADMIN_SETTINGS_KEYS = [
        'admin_attendance' => [
            'attendance_reminder_enabled',
            'face_recognition_enabled',
            'liveness_detection_enabled',
        ],
        'admin_backup' => [
            'auto_backup_enabled',
            'backup_schedule',
            'backup_include_files',
            'backup_location',
            'backup_retention_days',
        ],
        'admin_driver_management' => [
            'driver_approval_required',
        ],
        'admin_email' => [
            'email_from_address',
            'email_from_name',
            'smtp_enabled',
            'smtp_encryption',
            'smtp_host',
            'smtp_port',
        ],
        'admin_export' => [
            'export_enabled',
            'export_formats',
            'export_include_sensitive',
            'export_max_records',
        ],
        'admin_face_recognition' => [
            'face_matching_threshold',
            'face_recognition_provider',
            'max_face_images_per_driver',
            'min_face_confidence',
            'min_liveness_score',
        ],
        'admin_location' => [
            'geofence_enabled',
            'gps_tracking_enabled',
            'location_accuracy_required',
            'location_update_interval',
            'require_location_checkin',
        ],
        'admin_notifications' => [
            'attendance_notification_channel',
            'email_notifications_enabled',
            'notification_email',
            'notify_on_checkin',
            'notify_on_checkout',
        ],
        'admin_performance' => [
            'cache_enabled',
            'cache_ttl_minutes',
            'image_compression',
            'max_upload_size_mb',
            'query_optimization',
        ],
        'admin_reports' => [
            'report_auto_generate',
            'report_email_recipients',
            'report_format',
            'report_include_charts',
            'report_retention_days',
        ],
        'admin_security' => [
            'audit_log_retention_days',
            'encrypt_sensitive_data',
        ],
        'admin_system' => [
            'maintenance_mode',
            'max_login_attempts',
            'password_min_length',
            'require_password_change',
            'session_timeout',
        ],
        'driver_accessibility' => [
            'driver_font_size',
            'driver_high_contrast',
            'driver_keyboard_shortcuts',
            'driver_screen_reader',
        ],
        'driver_dashboard' => [
            'driver_dashboard_layout',
            'driver_refresh_interval',
            'driver_show_recent_activity',
            'driver_show_statistics',
            'driver_show_upcoming_events',
        ],
        'driver_data_usage' => [
            'driver_auto_load_images',
            'driver_data_saver_mode',
            'driver_sync_frequency',
        ],
        'driver_notifications' => [
            'attendance_notification_channel',
            'driver_browser_notify_checkin',
            'driver_browser_notify_checkout',
            'driver_notification_sound',
            'notify_checkin_reminder',
            'notify_checkout_reminder',
            'show_notifications',
            'driver_announcement_in_app',
            'driver_announcement_email',
        ],
        'driver_profile' => [
            'driver_allow_profile_updates',
            'driver_profile_photo_required',
            'driver_show_badge_number',
            'driver_show_email',
        ],
        'driver_reminders' => [
            'driver_checkin_reminder_time',
            'driver_checkout_reminder_time',
            'driver_reminder_before_minutes',
            'driver_reminder_repeat',
            'driver_reminder_snooze',
            'driver_reminders_enabled',
        ],
        'driver_security' => [
            'driver_two_factor_enabled',
            'driver_auto_lockout',
            'driver_lockout_minutes',
            'driver_session_timeout',
        ],
        'general' => [
            'company_name',
            'contact_email',
            'enable_backup',
            'enable_export',
            'enable_logging',
            'enable_notifications',
            'enable_registration',
            'enable_sync',
            'footer_text',
            'items_per_page',
            'max_upload_size',
            'phone_number',
            'session_lifetime',
            'site_description',
            'site_name',
            'support_email',
            'welcome_message',
        ],
    ];

    /** @var list<string> */
    private const DRIVER_GROUP_ORDER = [
        'driver_accessibility',
        'driver_notifications',
        'driver_reminders',
        'driver_dashboard',
        'driver_data_usage',
        'driver_profile',
        'driver_security',
    ];

    /** @var array<string, list<string>> */
    private const DRIVER_SETTINGS_KEYS = [
        'driver_accessibility' => [
            'driver_font_size',
            'driver_high_contrast',
            'driver_keyboard_shortcuts',
            'driver_screen_reader',
            'driver_animations',
        ],
        'driver_notifications' => [
            'show_notifications',
            'driver_notification_sound',
            'driver_browser_notify_checkin',
            'driver_browser_notify_checkout',
            'driver_email_notifications',
            'driver_email_on_checkin',
            'driver_email_on_checkout',
            'notify_checkin_reminder',
            'notify_checkout_reminder',
            'driver_announcement_in_app',
            'driver_announcement_email',
        ],
        'driver_reminders' => [
            'driver_reminders_enabled',
            'driver_checkin_reminder_time',
            'driver_checkout_reminder_time',
            'driver_reminder_before_minutes',
            'driver_reminder_repeat',
            'driver_reminder_snooze',
        ],
        'driver_dashboard' => [
            'driver_dashboard_layout',
            'driver_refresh_interval',
            'driver_show_recent_activity',
            'driver_show_statistics',
        ],
        'driver_data_usage' => [
            'driver_auto_load_images',
            'driver_sync_frequency',
        ],
        'driver_profile' => [
            'driver_allow_profile_updates',
            'driver_show_email',
        ],
        'driver_security' => [
            'driver_two_factor_enabled',
            'driver_session_timeout',
        ],
    ];

    /**
     * @return list<string>
     */
    private static function driverAllowedSettingKeys(): array
    {
        return array_merge(...array_values(self::DRIVER_SETTINGS_KEYS));
    }

    /**
     * @param array<string, list<string>> $keyOrderByGroup
     * @return list<string>
     */
    private static function allowedSettingKeys(array $keyOrderByGroup): array
    {
        return array_values(array_unique(array_merge(...array_values($keyOrderByGroup))));
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\Setting> $settings
     * @param list<string> $groupOrder
     * @param array<string, list<string>> $keyOrderByGroup
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\Setting>>
     */
    private static function orderGroupedSettings($settings, array $groupOrder, array $keyOrderByGroup)
    {
        $all = collect($settings->all());
        $grouped = $all->groupBy('group');
        $result = collect();

        foreach ($groupOrder as $group) {
            if (! $grouped->has($group)) {
                continue;
            }

            $groupItems = collect($grouped->get($group)->all());
            $desiredKeys = $keyOrderByGroup[$group] ?? [];
            $byKey = $groupItems->keyBy('key');

            $ordered = collect($desiredKeys)
                ->map(fn (string $key) => $byKey->get($key))
                ->filter()
                ->values();
            if ($ordered->isNotEmpty()) {
                $result->put($group, $ordered);
            }
        }

        return $result;
    }

    public function index(): View
    {
        $user = Auth::user();
        $role = $user->role ?? 'driver';

        // Ensure all settings are seeded (firstOrCreate will only create missing ones)
        // Check if we have a reasonable number of settings, if not, seed them
        $requiredKeys = ['driver_announcement_in_app', 'driver_announcement_email', 'backup_schedule'];
        $missingRequiredKeys = Setting::query()->whereIn('key', $requiredKeys)->count() < count($requiredKeys);

        if (Setting::count() < 100 || $missingRequiredKeys) {
            Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
        }

        // Keep face confidence and liveness settings under face recognition group.
        Setting::query()
            ->where('key', 'min_face_confidence')
            ->where('group', '!=', 'admin_face_recognition')
            ->update(['group' => 'admin_face_recognition']);
        Setting::query()
            ->where('key', 'min_liveness_score')
            ->where('group', '!=', 'admin_face_recognition')
            ->update(['group' => 'admin_face_recognition']);
        Setting::query()
            ->where('key', 'driver_two_factor_enabled')
            ->where('group', '!=', 'driver_security')
            ->update(['group' => 'driver_security']);

        // Filter settings based on user role
        $query = Setting::query();

        if ($role === 'admin') {
            $settings = self::orderGroupedSettings(
                $query
                    ->whereIn('group', array_keys(self::ADMIN_SETTINGS_KEYS))
                    ->whereIn('key', self::allowedSettingKeys(self::ADMIN_SETTINGS_KEYS))
                    ->get(),
                self::ADMIN_GROUP_ORDER,
                self::ADMIN_SETTINGS_KEYS
            );
        } else {
            $allowedKeys = self::driverAllowedSettingKeys();
            $settings = self::orderGroupedSettings(
                $query->whereIn('group', array_keys(self::DRIVER_SETTINGS_KEYS))
                    ->whereIn('key', $allowedKeys)
                    ->get(),
                self::DRIVER_GROUP_ORDER,
                self::DRIVER_SETTINGS_KEYS
            );
        }

        return view('settings.index', [
            'settings' => $settings,
            'userRole' => $role,
            'driverLocationSharingEnabled' => (bool) ($user?->location_sharing_enabled ?? false),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $role = $user->role ?? 'driver';

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        $changedSettings = [];
        $sideEffects = [];
        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting instanceof Setting) {
                continue;
            }

            // Check if user has permission to update this setting
            if ($role === 'driver') {
                if (! in_array($setting->key, self::driverAllowedSettingKeys(), true)) {
                    continue;
                }
            }

            $normalizedValue = $this->normalizeSettingValueByType($setting->type, $value);

            if ($setting->key === 'timezone' && is_string($normalizedValue) && $normalizedValue !== '') {
                try {
                    new \DateTimeZone($normalizedValue);
                } catch (\Throwable) {
                    continue;
                }
            }

            if ($setting->value != $normalizedValue) {
                $oldValue = $setting->value;
                Setting::query()->whereKey($setting->id)->update(['value' => $normalizedValue]);
                $changedSettings[$key] = ['old' => $oldValue, 'new' => $normalizedValue];
                $sideEffects[$key] = $normalizedValue;
            }
        }

        if (!empty($changedSettings)) {
            AuditLogger::log('updated', 'Setting', null, $changedSettings, null, 'Settings updated');
        }

        $this->applyRuntimeSideEffects($sideEffects);

        return redirect()->route('settings.index')->with('status', 'Settings updated successfully.');
    }

    /**
     * @param mixed $value
     */
    private function normalizeSettingValueByType(string $type, $value): string
    {
        return match ($type) {
            'boolean' => (string) ((bool) $value ? 1 : 0),
            'integer' => (string) (int) $value,
            'json' => is_string($value) ? (json_decode($value, true) !== null || trim($value) === 'null' ? $value : json_encode($value)) : json_encode($value),
            default => is_array($value) ? json_encode($value) : (string) $value,
        };
    }

    /**
     * @param array<string, string> $sideEffects
     */
    private function applyRuntimeSideEffects(array $sideEffects): void
    {
        if (array_key_exists('maintenance_mode', $sideEffects)) {
            if ($sideEffects['maintenance_mode'] === '1') {
                Artisan::call('down');
            } else {
                Artisan::call('up');
            }
        }
    }

    public function updateDriverLocationSharing(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'driver') {
            abort(403);
        }

        $enabled = $request->boolean('location_sharing_enabled');
        $user->location_sharing_enabled = $enabled;

        if (! $enabled) {
            $user->latitude = null;
            $user->longitude = null;
            $user->location_updated_at = null;
        }

        \App\Models\User::query()->whereKey($user->id)->update([
            'location_sharing_enabled' => $user->location_sharing_enabled,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'location_updated_at' => $user->location_updated_at,
        ]);

        AuditLogger::log(
            'updated',
            'User',
            $user->id,
            ['location_sharing_enabled' => $enabled ? '1' : '0'],
            null,
            $enabled ? 'Driver enabled live location sharing' : 'Driver disabled live location sharing'
        );

        return redirect()->route('settings.index')->with(
            'status',
            $enabled
                ? 'Live location sharing enabled. Admin can now track your route.'
                : 'Live location sharing disabled. Admin will see that your location is off.'
        );
    }
}
