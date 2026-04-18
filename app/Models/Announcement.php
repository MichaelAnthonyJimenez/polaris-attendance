<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'send_to_all',
        'created_by',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'send_to_all' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Drivers selected for this announcement (only used when send_to_all = false).
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_user', 'announcement_id', 'user_id');
    }

    public function isCurrentlyPublished(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        if ($this->published_at === null || $this->published_at->gt($now)) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->lte($now)) {
            return false;
        }

        return true;
    }
}

