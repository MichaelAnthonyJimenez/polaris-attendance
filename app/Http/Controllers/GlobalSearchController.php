<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $search = trim((string) $request->get('search', ''));

        if ($search === '') {
            return redirect()->route('users.index');
        }

        // Default landing page for global search.
        return redirect()->route('users.index', ['search' => $search]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('search', ''));

        if ($search === '') {
            return response()->json(['results' => []]);
        }

        $limit = 6;
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';

        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)->orWhere('email', 'like', $like);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn ($u) => [
                'type' => 'User',
                'label' => $u->name,
                'meta' => $u->email,
                'url' => route('users.index', ['search' => $u->email]),
            ]);

        $attendances = Attendance::query()
            ->with(['driver:id,name'])
            ->select(['id', 'driver_id', 'type', 'captured_at', 'device_id'])
            ->where(function ($q) use ($like) {
                $q->where('type', 'like', $like)
                    ->orWhere('device_id', 'like', $like)
                    ->orWhereHas('driver', fn ($dq) => $dq->where('name', 'like', $like));
            })
            ->latest('captured_at')
            ->limit($limit)
            ->get()
            ->map(function ($a) {
                $driverName = $a->driver?->name ?? 'Unknown';
                $when = $a->captured_at?->format('M d, H:i') ?? '';

                return [
                    'type' => 'Attendance',
                    'label' => $driverName,
                    'meta' => trim($a->type . ' · ' . $when),
                    'url' => route('attendance.index', ['search' => $driverName]) . '#attendance-row-' . $a->id,
                ];
            });

        $results = collect()
            ->concat($users)
            ->concat($attendances)
            ->values();

        return response()->json([
            'results' => $results,
        ]);
    }
}

