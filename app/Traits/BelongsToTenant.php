<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Static re-entry guard to prevent infinite recursion during Auth user resolution
            static $isResolving = false;
            if ($isResolving) {
                return;
            }

            $isResolving = true;
            try {
                $user = auth()->user();
                if ($user && $user->role === 'super_admin') {
                    return;
                }
            } finally {
                $isResolving = false;
            }

            if (! app()->bound('currentTenant')) {
                if (app()->runningUnitTests() || app()->runningInConsole()) {
                    $defaultTenant = Tenant::where('subdomain', 'default')->first();
                    if ($defaultTenant) {
                        $defaultTenant->makeCurrent();
                    }
                }
            }

            if (app()->bound('currentTenant')) {
                $tenant = app('currentTenant');
                if ($tenant) {
                    $builder->where('tenant_id', $tenant->id);
                }
            }
        });

        // 2. Automatically inject tenant_id when generating new records
        static::creating(function (Model $model) {
            if (! app()->bound('currentTenant')) {
                if (app()->runningUnitTests() || app()->runningInConsole()) {
                    $defaultTenant = Tenant::where('subdomain', 'default')->first();
                    if ($defaultTenant) {
                        $defaultTenant->makeCurrent();
                    }
                }
            }

            if (app()->bound('currentTenant')) {
                $tenant = app('currentTenant');
                if ($tenant && ! $model->tenant_id) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });
    }

    /**
     * Get the tenant that owns this record.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
