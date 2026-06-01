<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateResumeData extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'candidate_resume_data';

    protected $fillable = [
        'tenant_id',
        'candidate_application_id',
        'full_name',
        'email',
        'phone',
        'location',
        'linkedin_url',
        'portfolio_url',
        'total_experience_years',
        'current_company',
        'current_designation',
        'raw_text',
    ];

    /**
     * Get the candidate application linked to this parsed resume data.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
