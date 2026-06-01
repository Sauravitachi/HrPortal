<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobFeedLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'job_feed_logs';

    protected $fillable = [
        'tenant_id',
        'feed_type',
        'ip_address',
        'user_agent',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'accessed_at' => 'datetime',
        ];
    }
}
