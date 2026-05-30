<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'logo',
        'address',
        'contact_email',
        'subscription_plan',
        'subscription_expires_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'subscription_expires_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * Get the tenant that owns the company profile.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
