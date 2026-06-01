<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeParseLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'resume_parse_logs';

    protected $fillable = [
        'tenant_id',
        'candidate_application_id',
        'status',
        'error_message',
    ];

    /**
     * Get the candidate application linked to this parse transaction audit log.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
