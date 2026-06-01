<?php

namespace App\Services;

use App\Models\CandidateApplication;
use App\Models\CandidateEducation;
use App\Models\CandidateProject;
use App\Models\CandidateResumeData;
use App\Models\CandidateSkill;
use App\Services\AI\AIServiceInterface;
use App\Services\AI\GeminiAIService;
use App\Services\AI\MockAIService;
use App\Services\AI\OpenAIAIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResumeParserService
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
     * Parse the candidate's resume and save details in structured profile tables.
     */
    public function parse(CandidateApplication $application): CandidateResumeData
    {
        $rawText = $this->extractText($application->resume_path);

        // Forward raw text to AI service for structuring
        $parsed = $this->aiService->parseResume($rawText);

        return DB::transaction(function () use ($application, $parsed, $rawText) {
            // Delete old data if re-parsing
            $application->resumeData()->delete();
            $application->skills()->delete();
            $application->education()->delete();
            $application->projects()->delete();

            // 1. Create Base Resume Data
            $resumeData = CandidateResumeData::create([
                'tenant_id' => $application->tenant_id,
                'candidate_application_id' => $application->id,
                'full_name' => $parsed['full_name'] ?? $application->full_name,
                'email' => $parsed['email'] ?? $application->email,
                'phone' => $parsed['phone'] ?? $application->contact_number,
                'location' => $parsed['location'] ?? null,
                'linkedin_url' => $parsed['linkedin_url'] ?? null,
                'portfolio_url' => $parsed['portfolio_url'] ?? null,
                'total_experience_years' => $parsed['total_experience_years'] ?? 0.0,
                'current_company' => $parsed['current_company'] ?? null,
                'current_designation' => $parsed['current_designation'] ?? null,
                'raw_text' => $rawText,
            ]);

            // 2. Create Skills
            if (! empty($parsed['skills'])) {
                foreach ($parsed['skills'] as $skill) {
                    CandidateSkill::create([
                        'tenant_id' => $application->tenant_id,
                        'candidate_application_id' => $application->id,
                        'skill_name' => $skill['name'] ?? $skill,
                        'skill_type' => $skill['type'] ?? 'technical',
                    ]);
                }
            }

            // 3. Create Education
            if (! empty($parsed['education'])) {
                foreach ($parsed['education'] as $edu) {
                    CandidateEducation::create([
                        'tenant_id' => $application->tenant_id,
                        'candidate_application_id' => $application->id,
                        'degree' => $edu['degree'] ?? 'Degree',
                        'college' => $edu['college'] ?? 'College',
                        'passing_year' => $edu['passing_year'] ?? null,
                    ]);
                }
            }

            // 4. Create Projects
            if (! empty($parsed['projects'])) {
                foreach ($parsed['projects'] as $proj) {
                    CandidateProject::create([
                        'tenant_id' => $application->tenant_id,
                        'candidate_application_id' => $application->id,
                        'project_name' => $proj['name'] ?? 'Project',
                        'technologies_used' => $proj['technologies_used'] ?? null,
                    ]);
                }
            }

            return $resumeData;
        });
    }

    /**
     * Extract plain text from PDF or DOCX resume files.
     */
    protected function extractText(string $path): string
    {
        if (! Storage::disk('public')->exists($path)) {
            Log::warning("Resume file not found in storage: {$path}");

            return '';
        }

        $fullPath = Storage::disk('public')->path($path);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'docx') {
            return $this->extractTextFromDocx($fullPath);
        } elseif ($extension === 'pdf') {
            return $this->extractTextFromPdf($fullPath);
        }

        return '';
    }

    /**
     * Parse word docx using native ZipArchive parser.
     */
    protected function extractTextFromDocx(string $fullPath): string
    {
        $stripedText = '';
        $zip = new \ZipArchive;

        if ($zip->open($fullPath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();

                // Strip XML tags cleanly
                $stripedText = strip_tags($data);
                // Clean extra whitespaces
                $stripedText = preg_replace('/\s+/', ' ', $stripedText);
            } else {
                $zip->close();
            }
        }

        return trim($stripedText);
    }

    /**
     * Parse PDF text streams in pure PHP.
     */
    protected function extractTextFromPdf(string $fullPath): string
    {
        $content = @file_get_contents($fullPath);
        if (! $content) {
            return '';
        }

        // Extremely resilient text-stream collector for PDF contents
        $resultText = '';

        // Find text objects within BT (Begin Text) and ET (End Text)
        preg_match_all('/BT(.*?)ET/s', $content, $matches);

        if (! empty($matches[1])) {
            foreach ($matches[1] as $textObject) {
                // Match literal strings inside brackets: (literal string)
                preg_match_all('/\((.*?)\)/s', $textObject, $textStrings);
                if (! empty($textStrings[1])) {
                    foreach ($textStrings[1] as $str) {
                        // Decode octal escapes if any
                        $str = preg_replace_callback('/\\\\([0-7]{3})/', function ($octMatch) {
                            return chr(octdec($octMatch[1]));
                        }, $str);

                        // Strip backslashes escaping parens
                        $str = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $str);
                        $resultText .= $str.' ';
                    }
                }
            }
        }

        // If BT/ET parsing resulted in very little content (due to compression/encoding),
        // let's run a fallback regex to extract readable ASCII blocks from streams.
        if (strlen(trim($resultText)) < 50) {
            // Match plain alphanumeric text chunks that might represent keywords
            preg_match_all('/[\w\s\-\.\@\+\#\:\/]{5,100}/', strip_tags($content), $asciiMatches);
            if (! empty($asciiMatches[0])) {
                $resultText = implode(' ', array_slice($asciiMatches[0], 0, 500));
            }
        }

        return trim($resultText);
    }
}
