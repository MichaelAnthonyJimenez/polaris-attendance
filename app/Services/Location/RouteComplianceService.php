<?php

namespace App\Services\Location;

use App\Models\DriverRouteAssignment;

class RouteComplianceService
{
    public function evaluate(int $driverId, ?float $latitude, ?float $longitude, ?float $accuracyMeters): array
    {
        if ($latitude === null || $longitude === null) {
            return ['status' => 'unknown', 'reason' => 'missing_location'];
        }

        $assignment = DriverRouteAssignment::query()
            ->with('route')
            ->where('driver_id', $driverId)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $assignment || ! $assignment->route || ! $assignment->route->is_active) {
            return ['status' => 'unknown', 'reason' => 'no_assigned_route'];
        }

        $points = is_array($assignment->route->path_points) ? $assignment->route->path_points : [];
        if (count($points) < 2) {
            return ['status' => 'unknown', 'reason' => 'invalid_route_points'];
        }

        $distanceMeters = $this->distanceToPolylineMeters($latitude, $longitude, $points);
        $buffer = (float) ($assignment->route->buffer_meters ?? 100);
        $accuracy = max(0.0, (float) ($accuracyMeters ?? 0));
        $effectiveBuffer = $buffer + $accuracy;

        return [
            'status' => $distanceMeters <= $effectiveBuffer ? 'inside_buffer' : 'outside_buffer',
            'distance_to_route_m' => round($distanceMeters, 2),
            'buffer_m' => $buffer,
            'effective_buffer_m' => round($effectiveBuffer, 2),
            'accuracy_m' => $accuracy,
            'route_id' => $assignment->route->id,
            'route_name' => $assignment->route->name,
        ];
    }

    private function distanceToPolylineMeters(float $lat, float $lng, array $points): float
    {
        $best = INF;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $a = $points[$i];
            $b = $points[$i + 1];
            if (! isset($a['lat'], $a['lng'], $b['lat'], $b['lng'])) {
                continue;
            }
            $d = $this->distancePointToSegmentMeters($lat, $lng, (float) $a['lat'], (float) $a['lng'], (float) $b['lat'], (float) $b['lng']);
            if ($d < $best) {
                $best = $d;
            }
        }

        return is_finite($best) ? $best : INF;
    }

    private function distancePointToSegmentMeters(float $lat, float $lng, float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $x = $this->metersX($lng, $lat);
        $y = $this->metersY($lat);
        $x1 = $this->metersX($lng1, $lat1);
        $y1 = $this->metersY($lat1);
        $x2 = $this->metersX($lng2, $lat2);
        $y2 = $this->metersY($lat2);

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        if ($dx == 0.0 && $dy == 0.0) {
            return hypot($x - $x1, $y - $y1);
        }
        $t = (($x - $x1) * $dx + ($y - $y1) * $dy) / (($dx * $dx) + ($dy * $dy));
        $t = max(0.0, min(1.0, $t));
        $px = $x1 + ($t * $dx);
        $py = $y1 + ($t * $dy);

        return hypot($x - $px, $y - $py);
    }

    private function metersX(float $lng, float $latRef): float
    {
        return $lng * 111320 * cos(deg2rad($latRef));
    }

    private function metersY(float $lat): float
    {
        return $lat * 110540;
    }
}

