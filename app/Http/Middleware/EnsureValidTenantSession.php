<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession as SpatieEnsureValidTenantSession;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidTenantSession extends SpatieEnsureValidTenantSession
{
    /**
     * Handle an invalid tenant session.
     *
     * Instead of blocking the user with a 401 Unauthorized page when they switch
     * tenant subdomains, we gracefully invalidate the previous tenant's session,
     * regenerate the CSRF token, and redirect them to load a fresh page.
     *
     * @param  Request  $request
     * @return Response
     */
    protected function handleInvalidTenantSession($request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Pre-populate the session with the new tenant ID so the next request is immediately valid
        $sessionKey = 'ensure_valid_tenant_session_tenant_id';
        $request->session()->put($sessionKey, app($this->currentTenantContainerKey())->getKey());

        return redirect()->to($request->fullUrl());
    }
}
