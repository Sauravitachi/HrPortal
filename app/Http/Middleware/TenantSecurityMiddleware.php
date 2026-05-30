<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // 1. Super Admins bypass tenant checks as they are global platform operators
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $currentTenant = app('currentTenant');

        if (! $currentTenant) {
            abort(400, 'Tenant context is not established.');
        }

        // 2. Strict validation: verify user belongs to the current resolved tenant
        if ($user->tenant_id !== $currentTenant->id) {
            abort(403, 'Unauthorized access to this tenant.');
        }

        return $next($request);
    }
}
