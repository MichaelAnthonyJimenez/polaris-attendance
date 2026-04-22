<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'buffer_meters',
        'path_points',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'buffer_meters' => 'float',
        'path_points' => 'array',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(DriverRouteAssignment::class, 'operational_route_id');
    }
}

