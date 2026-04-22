<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Attendance $attendance,
        private readonly string $message,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'attendance_id' => $this->attendance->id,
            'driver_id' => $this->attendance->driver_id,
            'type' => $this->attendance->type,
            'captured_at' => $this->attendance->captured_at?->toIso8601String(),
        ];
    }
}

