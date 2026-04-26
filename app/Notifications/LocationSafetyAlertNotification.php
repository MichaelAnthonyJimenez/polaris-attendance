<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LocationSafetyAlertNotification extends Notification
{
    use Queueable;

    /**
     * @param array{code:string,title:string,message:string,severity:string} $risk
     */
    public function __construct(
        private readonly User $driver,
        private readonly array $risk,
        private readonly float $latitude,
        private readonly float $longitude,
        private readonly ?float $accuracyMeters,
        private readonly ?float $speedKph,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => ($this->driver->name ?? 'Driver') . ' triggered a location safety alert: ' . $this->risk['title'],
            'driver_id' => $this->driver->id,
            'driver_name' => $this->driver->name,
            'risk_code' => $this->risk['code'],
            'risk_title' => $this->risk['title'],
            'risk_severity' => $this->risk['severity'],
            'risk_message' => $this->risk['message'],
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'geo_accuracy' => $this->accuracyMeters,
            'speed_kph' => $this->speedKph,
            'captured_at' => now()->toIso8601String(),
        ];
    }
}

