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
        $rawText = mb_scrub($rawText, 'UTF-8');

        // Dynamic Exploded Spaces Collapse (helps reconstruct spaced PDF strings)
        if (preg_match_all('/\b\w\b/', $rawText) > strlen($rawText) * 0.1) {
            $rawText = preg_replace('/\s{2,}/', ' __SPACE__ ', $rawText);
            $rawText = str_replace(' ', '', $rawText);
            $rawText = str_replace('__SPACE__', ' ', $rawText);
        }

        // Forward raw text to AI service for structuring
        $parsed = $this->aiService->parseResume($rawText);

        return DB::transaction(function () use ($application, $parsed, $rawText) {
            // Delete old data if re-parsing
            $application->resumeData()->delete();
            $application->skills()->delete();
            $application->education()->delete();
            $application->projects()->delete();

            // Determine dynamic values prioritizing form inputs over static parser fallbacks
            $fullName = $parsed['full_name'] ?? $application->full_name;
            if (($fullName === 'John Doe' || empty($fullName)) && ! empty($application->full_name) && $application->full_name !== 'ATS Check Candidate') {
                $fullName = $application->full_name;
            }

            $email = $parsed['email'] ?? $application->email;
            if (($email === 'john.doe@example.com' || empty($email)) && ! empty($application->email) && $application->email !== 'ats-check@example.com') {
                $email = $application->email;
            }

            $phone = $parsed['phone'] ?? $application->contact_number;
            if (($phone === '+91 98765 43210' || empty($phone)) && ! empty($application->contact_number) && $application->contact_number !== '0000000000') {
                $phone = $application->contact_number;
            }

            // 1. Create Base Resume Data
            $resumeData = CandidateResumeData::create([
                'tenant_id' => $application->tenant_id,
                'candidate_application_id' => $application->id,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
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

    protected function extractTextFromPdf(string $fullPath): string
    {
        $content = @file_get_contents($fullPath);
        if (! $content) {
            return '';
        }

        $resultText = '';
        $offset = 0;

        // Binary-safe stream block isolation avoiding PCRE backtrack limits
        while (($startPos = strpos($content, 'stream', $offset)) !== false) {
            $dataStart = $startPos + 6;
            if (substr($content, $dataStart, 2) === "\r\n") {
                $dataStart += 2;
            } elseif (substr($content, $dataStart, 1) === "\n" || substr($content, $dataStart, 1) === "\r") {
                $dataStart += 1;
            }

            $endPos = strpos($content, 'endstream', $dataStart);
            if ($endPos === false) {
                break;
            }

            $streamData = substr($content, $dataStart, $endPos - $dataStart);
            $offset = $endPos + 9;

            // Attempt inflating FlateDecode streams
            $decompressed = @gzuncompress($streamData);
            if ($decompressed === false) {
                $decompressed = @gzinflate(substr($streamData, 2));
            }
            if ($decompressed === false) {
                $decompressed = @gzinflate($streamData);
            }

            if ($decompressed !== false && ! empty($decompressed)) {
                // To avoid reading binary font descriptor tables or CMap structures as text,
                // we ONLY parse streams that contain text drawing block operators (BT and ET).
                if (strpos($decompressed, 'BT') !== false && strpos($decompressed, 'ET') !== false) {
                    $btOffset = 0;
                    while (($btStart = strpos($decompressed, 'BT', $btOffset)) !== false) {
                        $etEnd = strpos($decompressed, 'ET', $btStart);
                        if ($etEnd === false) {
                            break;
                        }

                        $textBlock = substr($decompressed, $btStart + 2, $etEnd - ($btStart + 2));
                        $btOffset = $etEnd + 2;

                        // 1. Support hex-encoded glyph sequences (e.g. <00360044> or <0036> Tj)
                        preg_match_all('/<([0-9a-fA-F]+)>/', $textBlock, $hexMatches);
                        if (! empty($hexMatches[1])) {
                            foreach ($hexMatches[1] as $hexStr) {
                                $chunks = str_split($hexStr, 4);
                                foreach ($chunks as $chunk) {
                                    $dec = hexdec($chunk);

                                    // Detect if characters are shifted (standard shift in subsetted PDFs like Amiri or Canva is 29)
                                    $charVal = ($dec < 120 && $dec > 0) ? ($dec + 29) : $dec;
                                    if ($charVal >= 32 && $charVal <= 126) {
                                        $resultText .= chr($charVal);
                                    }
                                }
                            }
                            $resultText .= ' ';
                        }

                        // 2. Support standard parenthesized strings (e.g. (Text) Tj)
                        $strOffset = 0;
                        while (($strStart = strpos($textBlock, '(', $strOffset)) !== false) {
                            $strEnd = false;
                            $searchOffset = $strStart + 1;

                            while (($parenPos = strpos($textBlock, ')', $searchOffset)) !== false) {
                                $escaped = false;
                                $checkPos = $parenPos - 1;
                                while ($checkPos >= $strStart && $textBlock[$checkPos] === '\\') {
                                    $escaped = ! $escaped;
                                    $checkPos--;
                                }
                                if (! $escaped) {
                                    $strEnd = $parenPos;
                                    break;
                                }
                                $searchOffset = $parenPos + 1;
                            }

                            if ($strEnd === false) {
                                break;
                            }

                            $strText = substr($textBlock, $strStart + 1, $strEnd - ($strStart + 1));
                            $resultText .= $strText.' ';
                            $strOffset = $strEnd + 1;
                        }

                        $resultText .= "\n";
                    }
                }
            }
        }

        // Clean character replacements, octals, backslashes, etc.
        $resultText = preg_replace_callback('/\\\\([0-7]{3})/', function ($octMatch) {
            return chr(octdec($octMatch[1]));
        }, $resultText);
        $resultText = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $resultText);

        // If decompression yielded a reasonable length, return it!
        if (strlen(trim($resultText)) > 50) {
            return trim($resultText);
        }

        // Fallback: Original resilient uncompressed text stream collector
        preg_match_all('/BT(.*?)ET/s', $content, $matches);

        if (! empty($matches[1])) {
            foreach ($matches[1] as $textObject) {
                preg_match_all('/\((.*?)\)/s', $textObject, $textStrings);
                if (! empty($textStrings[1])) {
                    foreach ($textStrings[1] as $str) {
                        $resultText .= $str.' ';
                    }
                }
            }
        }

        // Final fallback regex if text is still extremely sparse
        if (strlen(trim($resultText)) < 50) {
            preg_match_all('/[\w\s\-\.\@\+\#\:\/]{5,100}/', strip_tags($content), $asciiMatches);
            if (! empty($asciiMatches[0])) {
                $resultText = implode(' ', array_slice($asciiMatches[0], 0, 500));
            }
        }

        return trim($resultText);
    }
}
