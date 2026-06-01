<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CandidateApplication extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'job_post_id', 'full_name', 'email', 'contact_number', 'resume_path', 'status'];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function matchScore(): HasOne
    {
        return $this->hasOne(CandidateMatchScore::class);
    }

    public function resumeData(): HasOne
    {
        return $this->hasOne(CandidateResumeData::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class);
    }

    public function education(): HasMany
    {
        return $this->hasMany(CandidateEducation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(CandidateProject::class);
    }

    public function generatedQuestions(): HasMany
    {
        return $this->hasMany(AiInterviewQuestion::class);
    }
}
