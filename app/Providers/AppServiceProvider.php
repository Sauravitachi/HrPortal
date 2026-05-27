<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

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
