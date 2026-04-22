<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverRouteAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'operational_route_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(OperationalRoute::class, 'operational_route_id');
    }
}

