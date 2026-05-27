<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(401);
        }

        $userRole = $request->user()->role;

        // If the user's role is in the allowed roles list, let them pass
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // If user is super_admin, they bypass all role blocks
        if ($userRole === 'super_admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
