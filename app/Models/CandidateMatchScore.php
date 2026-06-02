<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateMatchScore extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'candidate_application_id',
        'match_score',
        'analysis_summary',
        'matched_keywords',
        'missing_keywords',
        'generated_interview_questions',
        'strengths',
        'missing_skills',
        'experience_gap',
        'hiring_recommendation',
        'evaluation_scorecard',
        'feedback_form',
    ];

    protected function casts(): array
    {
        return [
            'matched_keywords' => 'array',
            'missing_keywords' => 'array',
            'generated_interview_questions' => 'array',
            'strengths' => 'array',
            'missing_skills' => 'array',
            'evaluation_scorecard' => 'array',
            'feedback_form' => 'array',
        ];
    }

    /**
     * Get the candidate application linked to this score.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
