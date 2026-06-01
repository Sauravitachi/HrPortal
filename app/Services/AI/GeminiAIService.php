<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAIService implements AIServiceInterface
{
    protected string $apiKey;

    protected string $model = 'gemini-1.5-flash';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
    }

    /**
     * Parse raw resume text into structured candidate information.
     */
    public function parseResume(string $resumeText): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API key is not configured. Falling back to MockAIService.');

            return (new MockAIService)->parseResume($resumeText);
        }

        try {
            $prompt = 'You are a professional HR Resume Parser. Extract the following fields from the resume text and return STRICTLY a JSON object with no markdown fences, headers, or surrounding text.
            If a field is not found, leave it as null.
            
            JSON Structure:
            {
              "full_name": "string",
              "email": "string",
              "phone": "string",
              "location": "string",
              "linkedin_url": "string",
              "portfolio_url": "string",
              "total_experience_years": float,
              "current_company": "string",
              "current_designation": "string",
              "skills": [
                { "name": "string", "type": "technical|soft" }
              ],
              "education": [
                { "degree": "string", "college": "string", "passing_year": integer }
              ],
              "projects": [
                { "name": "string", "technologies_used": "string" }
              ]
            }
            
            Resume Text:
            '.$resumeText;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

                return json_decode($text, true) ?: (new MockAIService)->parseResume($resumeText);
            }

            Log::error('Gemini API Error: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: '.$e->getMessage());
        }

        return (new MockAIService)->parseResume($resumeText);
    }

    /**
     * Compare parsed resume data against job requirements to calculate scores & recommendations.
     */
    public function screenCandidate(array $parsedResume, array $jobRequirement): array
    {
        if (empty($this->apiKey)) {
            return (new MockAIService)->screenCandidate($parsedResume, $jobRequirement);
        }

        try {
            $prompt = 'You are a professional HR Candidate Assessment Engine. Score the candidate against the job requirements.
            Ensure you output EXACTLY a JSON object matching this structure with no markdown formatting.
            Weights to factor: Skills Match = 40%, Experience Match = 30%, Education Match = 15%, Certifications = 10%, Projects Relevance = 5%.
            
            JSON Structure:
            {
              "match_score": integer (0-100),
              "analysis_summary": "string (detailed summary evaluation)",
              "strengths": ["string"],
              "missing_skills": ["string"],
              "experience_gap": "string",
              "hiring_recommendation": "Strongly Recommended|Recommended for Interview|Hold for Review|Do Not Hire",
              "evaluation_scorecard": {
                "skills_evaluation": { "score": integer, "feedback": "string" },
                "experience_evaluation": { "score": integer, "feedback": "string" },
                "project_relevance": { "score": integer, "feedback": "string" },
                "educational_alignment": { "score": integer, "feedback": "string" }
              },
              "feedback_form": {
                "strengths_summary": "string",
                "weaknesses_summary": "string",
                "overall_fit": "string"
              }
            }

            Job Requirements:
            Title: '.($jobRequirement['title'] ?? '').'
            Experience Required: '.($jobRequirement['experience_required'] ?? '').'
            Description: '.($jobRequirement['description'] ?? '').'

            Candidate Parsed Profile:
            '.json_encode($parsedResume);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

                return json_decode($text, true) ?: (new MockAIService)->screenCandidate($parsedResume, $jobRequirement);
            }

            Log::error('Gemini API Error: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: '.$e->getMessage());
        }

        return (new MockAIService)->screenCandidate($parsedResume, $jobRequirement);
    }

    /**
     * Generate customized interview questions based on candidate profile and job requirements.
     */
    public function generateInterviewQuestions(array $parsedResume, array $jobRequirement): array
    {
        if (empty($this->apiKey)) {
            return (new MockAIService)->generateInterviewQuestions($parsedResume, $jobRequirement);
        }

        try {
            $prompt = 'You are a professional Interview Panel Assessor. Generate customized interview questions for this candidate.
            Ensure you output EXACTLY a JSON array matching this structure with no markdown formatting.
            Generate a mix of technical, behavioral, scenario, and problem_solving questions of easy, medium, and hard difficulty.
            
            JSON Structure:
            [
              {
                "question": "string",
                "category": "technical|behavioral|scenario|problem_solving",
                "difficulty": "easy|medium|hard",
                "suggested_answer": "string (rubric answer)"
              }
            ]

            Job Requirements:
            Title: '.($jobRequirement['title'] ?? '').'
            Description: '.($jobRequirement['description'] ?? '').'

            Candidate Parsed Profile:
            '.json_encode($parsedResume);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '[]';

                return json_decode($text, true) ?: (new MockAIService)->generateInterviewQuestions($parsedResume, $jobRequirement);
            }

            Log::error('Gemini API Error: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: '.$e->getMessage());
        }

        return (new MockAIService)->generateInterviewQuestions($parsedResume, $jobRequirement);
    }
}
