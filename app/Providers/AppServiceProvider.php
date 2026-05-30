<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Dynamic Spatie Permissions Team scoping based on active Tenant
        Event::listen(
            MadeTenantCurrentEvent::class,
            function (MadeTenantCurrentEvent $event) {
                if ($event->tenant) {
                    setPermissionsTeamId($event->tenant->id);
                }
            }
        );

        // 2. Global Gate before-filter to authorize Super Admins for all abilities
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        // 3. Dynamic tenant binding for test execution and database seeders
        if (app()->runningUnitTests() || app()->runningInConsole()) {
            try {
                if (Schema::hasTable('tenants')) {
                    $defaultTenant = Tenant::where('subdomain', 'default')->first();
                    if ($defaultTenant) {
                        $defaultTenant->makeCurrent();
                    }
                }
            } catch (\Exception $e) {
                // Ignore exceptions during initial migrations setup
            }
        }

        Gate::define('super-admin', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('hr-manager', function (User $user) {
            return $user->isHrManager();
        });

        Gate::define('employee', function (User $user) {
            return $user->isEmployee();
        });
    }
}
