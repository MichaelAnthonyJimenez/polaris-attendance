<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'badge_number',
        'email',
        'phone',
        'vehicle_number',
        'active',
        'user_id',
    ];

    public function faces(): HasMany
    {
        return $this->hasMany(DriverFace::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}

