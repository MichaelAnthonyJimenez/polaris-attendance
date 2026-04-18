<?php

namespace App\Models;

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
        'meta' => 'array',
        'synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}

