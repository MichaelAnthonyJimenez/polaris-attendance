<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\DeepFace\DeepFaceRecognitionClient;
use App\Services\DeepFace\DeepFaceVerificationClient;
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
        $this->app->singleton(DeepFaceRecognitionClient::class, function () {
            return new DeepFaceRecognitionClient(
                rtrim((string) config('services.deepface.base_url', ''), '/'),
                (string) config('services.deepface.recognition_api_key', ''),
            );
        });

        $this->app->singleton(DeepFaceVerificationClient::class, function () {
            return new DeepFaceVerificationClient(
                rtrim((string) config('services.deepface.base_url', ''), '/'),
                (string) config('services.deepface.verification_api_key', ''),
            );
        });
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
