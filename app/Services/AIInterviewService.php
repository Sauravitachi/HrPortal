<?php

namespace App\Services;

use App\Models\AiInterviewQuestion;
use App\Models\CandidateApplication;
use App\Models\CandidateMatchScore;
use App\Models\JobPost;
use App\Services\AI\AIServiceInterface;
use App\Services\AI\GeminiAIService;
use App\Services\AI\MockAIService;
use App\Services\AI\OpenAIAIService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AIInterviewService
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
     * Generate customized interview questions and evaluation scorecard metrics.
     */
    public function generateQuestions(CandidateApplication $application, JobPost $job): Collection
    {
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
            'description' => $job->description,
        ];

        // Call AI service to generate interview questions
        $questions = $this->aiService->generateInterviewQuestions($parsedResume, $jobRequirement);

        return DB::transaction(function () use ($application, $questions) {
            // Delete old questions if regenerating
            AiInterviewQuestion::where('candidate_application_id', $application->id)->delete();

            $savedQuestions = collect();

            foreach ($questions as $q) {
                $savedQuestions->push(
                    AiInterviewQuestion::create([
                        'tenant_id' => $application->tenant_id,
                        'candidate_application_id' => $application->id,
                        'question' => $q['question'] ?? 'Question',
                        'category' => $q['category'] ?? 'technical',
                        'difficulty' => $q['difficulty'] ?? 'medium',
                        'suggested_answer' => $q['suggested_answer'] ?? null,
                    ])
                );
            }

            // Sync with candidate match scores table for backward compatibility
            $matchScore = CandidateMatchScore::where('candidate_application_id', $application->id)->first();
            if ($matchScore) {
                $matchScore->update([
                    'generated_interview_questions' => $questions,
                ]);
            }

            return $savedQuestions;
        });
    }
}
