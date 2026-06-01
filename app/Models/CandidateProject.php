<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProject extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'candidate_projects';

    protected $fillable = [
        'tenant_id',
        'candidate_application_id',
        'project_name',
        'technologies_used',
    ];

    /**
     * Get the candidate application linked to this project entry.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
