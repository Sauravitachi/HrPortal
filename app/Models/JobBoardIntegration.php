<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBoardIntegration extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'job_board_integrations';

    protected $fillable = [
        'tenant_id',
        'platform',
        'api_key',
        'api_secret',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }
}
