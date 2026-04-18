<?php

namespace App\Http\Middleware;

use App\Models\DriverVerification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriverVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ($user->role ?? null) !== 'driver') {
            return $next($request);
        }

        // Allow access to verification flow + logout without being verified.
        if ($request->routeIs('verification.*') || $request->routeIs('driver-verification.store') || $request->routeIs('logout')) {
            return $next($request);
        }

        $approved = DriverVerification::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();

        if (! $approved) {
            return redirect()
                ->route('verification.required')
                ->with('error', 'Verification required before you can access the system.');
        }

        return $next($request);
    }
}

