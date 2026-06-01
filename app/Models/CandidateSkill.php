<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSkill extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'candidate_skills';

    protected $fillable = [
        'tenant_id',
        'candidate_application_id',
        'skill_name',
        'skill_type',
    ];

    /**
     * Get the candidate application linked to this skill.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
