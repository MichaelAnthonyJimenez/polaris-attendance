<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<string>  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        $userRole = is_string($user?->role) ? mb_strtolower(trim($user->role)) : null;
        $allowed = array_values(array_filter(array_map(
            static fn ($r) => is_string($r) ? mb_strtolower(trim($r)) : null,
            $roles
        )));

        if (! $user || (! empty($allowed) && ($userRole === null || ! in_array($userRole, $allowed, true)))) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}

