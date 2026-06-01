<?php

namespace App\Services;

use App\Models\CandidateApplication;
use App\Models\CandidateMatchScore;
use App\Models\JobPost;
use App\Services\AI\AIServiceInterface;
use App\Services\AI\GeminiAIService;
use App\Services\AI\MockAIService;
use App\Services\AI\OpenAIAIService;
use Illuminate\Support\Facades\DB;

class CandidateScoringService
{
    protected AIServiceInterface $aiService;

    public function __construct()
    {
        // Select driver dynamically: prefers Gemini, then OpenAI, fallback to Mock
        if (! empty(env('GEMINI_API_KEY'))) {
            $this->aiService = new GeminiAIService;
        } elseif (! empty(env('OPENAI_API_KEY'))) {
            $this->aiService = new OpenAIAIService;
        } else {
            $this->aiService = new MockAIService;
        }
    }

    /**
     * Compute candidate match scores against job requirements and store in database.
     */
    public function score(CandidateApplication $application, JobPost $job): CandidateMatchScore
    {
        // Load relationships
        $application->load(['resumeData', 'skills', 'education', 'projects']);

        $parsedResume = [
            'full_name' => $application->resumeData->full_name ?? $application->full_name,
            'total_experience_years' => floatval($application->resumeData->total_experience_years ?? 0.0),
            'skills' => $application->skills->map(fn ($s) => ['name' => $s->skill_name, 'type' => $s->skill_type])->toArray(),
            'education' => $application->education->map(fn ($e) => ['degree' => $e->degree, 'college' => $e->college, 'passing_year' => $e->passing_year])->toArray(),
            'projects' => $application->projects->map(fn ($p) => ['name' => $p->project_name, 'technologies_used' => $p->technologies_used])->toArray(),
        ];

        $jobRequirement = [
            'title' => $job->title,
            'experience_required' => $job->experience_required,
            'description' => $job->description,
        ];

        // Call AI service for assessment
        $assessment = $this->aiService->screenCandidate($parsedResume, $jobRequirement);

        return DB::transaction(function () use ($application, $assessment) {
            // Delete old match scores if re-scoring
            CandidateMatchScore::where('candidate_application_id', $application->id)->delete();

            return CandidateMatchScore::create([
                'tenant_id' => $application->tenant_id,
                'candidate_application_id' => $application->id,
                'match_score' => intval($assessment['match_score'] ?? 50),
                'analysis_summary' => $assessment['analysis_summary'] ?? 'AI screening completed successfully.',
                'matched_keywords' => $assessment['strengths'] ?? ($assessment['matched_keywords'] ?? []),
                'missing_keywords' => $assessment['missing_skills'] ?? ($assessment['missing_keywords'] ?? []),
                'strengths' => $assessment['strengths'] ?? [],
                'missing_skills' => $assessment['missing_skills'] ?? [],
                'experience_gap' => $assessment['experience_gap'] ?? null,
                'hiring_recommendation' => $assessment['hiring_recommendation'] ?? 'Hold for Review',
                'evaluation_scorecard' => $assessment['evaluation_scorecard'] ?? null,
                'feedback_form' => $assessment['feedback_form'] ?? null,
                'generated_interview_questions' => null, // Filled by Interview Question Service
            ]);
        });
    }
}
