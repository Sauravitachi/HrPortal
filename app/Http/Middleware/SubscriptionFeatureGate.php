<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionFeatureGate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $currentTenant = app('currentTenant');

        if (! $currentTenant) {
            abort(400, 'Tenant context is not established.');
        }

        $company = $currentTenant->company;
        $plan = $company ? $company->subscription_plan : 'free';

        // Evaluate feature accessibility rules based on active subscription
        $allowed = match ($feature) {
            'basic_recruitment' => in_array($plan, ['basic', 'premium']),
            'advanced_ats' => $plan === 'premium',
            'ai_screening' => $plan === 'premium',
            default => false,
        };

        if (! $allowed) {
            abort(403, 'This feature requires a higher subscription plan.');
        }

        return $next($request);
    }
}
