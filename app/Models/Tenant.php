<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

class Tenant extends SpatieTenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
    ];

    /**
     * Get the company profile associated with the tenant.
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }
}
