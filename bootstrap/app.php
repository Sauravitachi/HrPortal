<?php

use App\Http\Middleware\EnsureValidTenantSession;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SubscriptionFeatureGate;
use App\Http\Middleware\TenantSecurityMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', [
            NeedsTenant::class,
            EnsureValidTenantSession::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'tenant.security' => TenantSecurityMiddleware::class,
            'subscription.feature' => SubscriptionFeatureGate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
