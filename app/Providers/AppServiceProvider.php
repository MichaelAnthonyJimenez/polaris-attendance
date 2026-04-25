<?php

namespace App\Providers;

use App\Models\Setting;
use DateTimeZone;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // DeepFace API services removed - using local Python services instead
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureApplicationTimezone();
    }

    /**
     * Use the admin-configured timezone (Settings → timezone) when valid,
     * otherwise keep config from APP_TIMEZONE / default UTC.
     */
    private function configureApplicationTimezone(): void
    {
        $fallback = config('app.timezone') ?: 'UTC';

        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $candidate = Setting::get('timezone');
            if (! is_string($candidate) || $candidate === '') {
                return;
            }

            new DateTimeZone($candidate);
            config(['app.timezone' => $candidate]);
            date_default_timezone_set($candidate);
        } catch (\Throwable) {
            config(['app.timezone' => $fallback]);
            date_default_timezone_set($fallback);
        }
    }
}
