<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInterviewQuestion extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'ai_interview_questions';

    protected $fillable = [
        'tenant_id',
        'candidate_application_id',
        'question',
        'category',
        'difficulty',
        'suggested_answer',
    ];

    /**
     * Get the candidate application linked to this interview question.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
