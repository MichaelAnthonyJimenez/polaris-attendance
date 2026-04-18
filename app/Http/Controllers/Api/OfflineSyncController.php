<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfflineSyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?: $request->input('device_token');
        $device = $token ? Device::where('api_token', $token)->first() : null;

        if (! $device) {
            return response()->json(['message' => 'Unauthorized device'], 401);
        }

        $payload = $request->validate([
            'events' => ['required', 'array'],
            'events.*.driver_id' => ['required', 'exists:users,id'],
            'events.*.type' => ['required', 'in:check_in,check_out'],
            'events.*.captured_at' => ['nullable', 'date'],
            'events.*.face_confidence' => ['nullable', 'numeric'],
            'events.*.liveness_score' => ['nullable', 'numeric'],
            'events.*.device_ref' => ['nullable', 'string', 'max:100'],
        ]);

        $stored = [];

        foreach ($payload['events'] as $event) {
            $stored[] = Attendance::create([
                'driver_id' => $event['driver_id'],
                'type' => $event['type'],
                'captured_at' => $event['captured_at'] ?? now(),
                'face_confidence' => $event['face_confidence'] ?? null,
                'liveness_score' => $event['liveness_score'] ?? null,
                'device_id' => $event['device_ref'] ?? $device->id,
                'synced' => true,
                'synced_at' => now(),
                'meta' => ['synced_via' => 'offline_api'],
            ]);
        }

        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'stored' => count($stored),
            'device' => $device->only(['id', 'name']),
        ]);
    }
}

