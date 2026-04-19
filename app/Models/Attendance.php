<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'type',
        'status',
        'total_hours',
        'captured_at',
        'face_confidence',
        'liveness_score',
        'image_path',
        'device_id',
        'meta',
        'synced',
        'synced_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'meta' => 'array',
        'synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /**
     * For a check-out, find the check-in that closes the open interval (no other check-out between that check-in and this check-out).
     */
    public static function findPairedCheckInForCheckout(int $driverId, Carbon $checkOutAt): ?self
    {
        $candidates = static::query()
            ->where('driver_id', $driverId)
            ->where('type', 'check_in')
            ->where('captured_at', '<', $checkOutAt)
            ->orderByDesc('captured_at')
            ->limit(100)
            ->get();

        foreach ($candidates as $checkIn) {
            if (! $checkIn instanceof self) {
                continue;
            }
            $hasCheckoutBetween = static::query()
                ->where('driver_id', $driverId)
                ->where('type', 'check_out')
                ->where('captured_at', '>', $checkIn->captured_at)
                ->where('captured_at', '<', $checkOutAt)
                ->exists();

            if (! $hasCheckoutBetween) {
                return $checkIn;
            }
        }

        return null;
    }

    public static function hoursBetween(?Carbon $start, Carbon $end): ?float
    {
        if ($start === null) {
            return null;
        }
        if ($end->lt($start)) {
            return null;
        }
        $seconds = $end->getTimestamp() - $start->getTimestamp();
        $hours = round($seconds / 3600, 2);

        return $hours >= 0 ? $hours : null;
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}

