<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        // Demo coordinates: derive deterministic pseudo-lat/lng from driver id
        $driverLocations = Driver::select('id', 'name', 'badge_number', 'active')
            ->get()
            ->map(function ($driver) {
                $seed = crc32((string) $driver->id);
                $lat = 14.55 + (($seed % 1000) / 100000); // around Manila area
                $lng = 121.0 + ((($seed >> 8) % 1000) / 100000);
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'badge' => $driver->badge_number,
                    'active' => (bool) $driver->active,
                    'lat' => $lat,
                    'lng' => $lng,
                ];
            });

        return view('locations.index', [
            'driverLocations' => $driverLocations,
        ]);
    }
}

