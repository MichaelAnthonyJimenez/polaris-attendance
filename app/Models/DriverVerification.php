<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverVerification extends Model
{
    use HasFactory;

    protected $table = 'driver_verifications';

    protected $fillable = [
        'user_id',
        'driver_id',
        'verification_method',
        'type',
        'status',
        'manual_form_data',
        'reason',
        'admin_notes',
        'face_image_path',
        'selfie_with_id_path',
        'id_image_path',
        'id_image_back_path',
        'reviewer_id',
        'reviewed_at',
    ];

    protected $casts = [
        'manual_form_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}

