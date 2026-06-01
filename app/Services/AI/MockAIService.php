<?php

namespace App\Services\AI;

class MockAIService implements AIServiceInterface
{
    /**
     * Parse raw resume text into structured candidate information.
     */
    public function parseResume(string $resumeText): array
    {
        // Simple regex checks on the text to make the mock somewhat dynamic
        $fullName = 'John Doe';
        if (preg_match('/([A-Z][a-z]+ [A-Z][a-z]+)/', $resumeText, $matches)) {
            $fullName = $matches[1];
        }

        $email = 'john.doe@example.com';
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $resumeText, $matches)) {
            $email = $matches[0];
        }

        $phone = '+91 98765 43210';
        if (preg_match('/(\+?[0-9\s-]{10,15})/', $resumeText, $matches)) {
            $phone = trim($matches[0]);
        }

        $totalExp = 5.0;
        if (preg_match('/(\d+)\+?\s*years?/i', $resumeText, $matches)) {
            $totalExp = (float) $matches[1];
        }

        // Return a beautifully structured parsed profile
        return [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'location' => 'Bengaluru, India',
            'linkedin_url' => 'https://linkedin.com/in/'.strtolower(str_replace(' ', '', $fullName)),
            'portfolio_url' => 'https://'.strtolower(str_replace(' ', '', $fullName)).'.dev',
            'total_experience_years' => $totalExp,
            'current_company' => 'Innovate Tech Solutions',
            'current_designation' => 'Senior Software Engineer',
            'skills' => [
                ['name' => 'Laravel', 'type' => 'technical'],
                ['name' => 'PHP', 'type' => 'technical'],
                ['name' => 'MySQL', 'type' => 'technical'],
                ['name' => 'React', 'type' => 'technical'],
                ['name' => 'Docker', 'type' => 'technical'],
                ['name' => 'REST APIs', 'type' => 'technical'],
                ['name' => 'Communication', 'type' => 'soft'],
                ['name' => 'Problem Solving', 'type' => 'soft'],
                ['name' => 'Team Collaboration', 'type' => 'soft'],
            ],
            'education' => [
                [
                    'degree' => 'Bachelor of Technology in Computer Science',
                    'college' => 'National Institute of Technology',
                    'passing_year' => 2019,
                ],
            ],
            'projects' => [
                [
                    'name' => 'E-Commerce Microservices Engine',
                    'technologies_used' => 'Laravel, RabbitMQ, Redis, Docker',
                ],
                [
                    'name' => 'HRMS Automation Hub',
                    'technologies_used' => 'PHP, Vue.js, Tailwind CSS',
                ],
            ],
        ];
    }

    /**
     * Compare parsed resume data against job requirements to calculate scores & recommendations.
     */
    public function screenCandidate(array $parsedResume, array $jobRequirement): array
    {
        // Simple match calculation for the mock based on skill overlap
        $jobTitle = $jobRequirement['title'] ?? '';
        $jobDesc = strtolower($jobRequirement['description'] ?? '');
        $skills = $parsedResume['skills'] ?? [];
        $matched = [];
        $missing = [];

        // Simple keywords to test overlap
        $keywords = ['laravel', 'php', 'mysql', 'docker', 'aws', 'kubernetes', 'vue', 'react', 'typescript'];
        foreach ($keywords as $kw) {
            $hasInResume = false;
            foreach ($skills as $s) {
                if (strtolower($s['name']) === $kw) {
                    $hasInResume = true;
                    break;
                }
            }

            $hasInJob = str_contains($jobDesc, $kw) || str_contains(strtolower($jobTitle), $kw);

            if ($hasInJob) {
                if ($hasInResume) {
                    $matched[] = ucfirst($kw);
                } else {
                    $missing[] = ucfirst($kw);
                }
            }
        }

        // Make sure matched/missing aren't empty for display purposes
        if (empty($matched)) {
            $matched = ['PHP', 'Laravel', 'REST APIs', 'MySQL'];
        }
        if (empty($missing)) {
            $missing = ['AWS', 'Kubernetes'];
        }

        // Calculate score
        $skillsScore = min(100, count($matched) * 20);
        $experienceScore = $parsedResume['total_experience_years'] >= 5.0 ? 100 : ($parsedResume['total_experience_years'] / 5.0) * 100;

        $matchScore = intval(($skillsScore * 0.4) + ($experienceScore * 0.3) + (85 * 0.15) + (90 * 0.1) + (80 * 0.05));
        $matchScore = max(30, min(100, $matchScore)); // boundaries

        $recommendation = 'Recommended for Interview';
        if ($matchScore >= 90) {
            $recommendation = 'Strongly Recommended';
        } elseif ($matchScore < 60) {
            $recommendation = 'Do Not Hire';
        } elseif ($matchScore < 75) {
            $recommendation = 'Hold for Review';
        }

        $experienceGap = 'No experience gap identified. Candidate possesses '.$parsedResume['total_experience_years'].' years of industry experience, aligning well with the requirements.';
        if ($parsedResume['total_experience_years'] < 4.0) {
            $experienceGap = 'Candidate has '.$parsedResume['total_experience_years'].' years of experience, slightly below the typical senior-level requirement of 5+ years.';
        }

        return [
            'match_score' => $matchScore,
            'analysis_summary' => 'Candidate exhibits strong credentials matching the core requirements. Possesses key competencies in '.implode(', ', $matched).' with a proven record in enterprise web environments.',
            'strengths' => $matched,
            'missing_skills' => $missing,
            'experience_gap' => $experienceGap,
            'hiring_recommendation' => $recommendation,
            'evaluation_scorecard' => [
                'skills_evaluation' => [
                    'score' => $skillsScore,
                    'feedback' => 'Demonstrates key backend capabilities. Strong syntax grasp.',
                ],
                'experience_evaluation' => [
                    'score' => intval($experienceScore),
                    'feedback' => 'Professional background displays good longevity in core roles.',
                ],
                'project_relevance' => [
                    'score' => 85,
                    'feedback' => 'Projects show appropriate architecture design (microservices, clean coding).',
                ],
                'educational_alignment' => [
                    'score' => 90,
                    'feedback' => 'Possesses direct CS degree from a reputed institution.',
                ],
            ],
            'feedback_form' => [
                'strengths_summary' => 'Solid technical portfolio, clean documentation, architectural understanding.',
                'weaknesses_summary' => 'Slight skill deficit in cloud scaling tools (AWS, K8s).',
                'overall_fit' => 'Highly aligned for core product engineering requirements.',
            ],
        ];
    }

    /**
     * Generate customized interview questions based on candidate profile and job requirements.
     */
    public function generateInterviewQuestions(array $parsedResume, array $jobRequirement): array
    {
        return [
            [
                'question' => 'Explain the lifecycle of a request in Laravel and how you would register a custom middleware in Laravel 12.',
                'category' => 'technical',
                'difficulty' => 'hard',
                'suggested_answer' => 'Requests flow through public/index.php, boostrapping providers, through the HTTP Kernel pipelines. In Laravel 12, custom middleware are registered declaratively inside bootstrap/app.php using Application::configure()->withMiddleware().',
            ],
            [
                'question' => 'Describe a scenario where you resolved an N+1 query problem in an Eloquent model. How did you diagnose and solve it?',
                'category' => 'technical',
                'difficulty' => 'medium',
                'suggested_answer' => 'Usually diagnosed using Laravel Pail or Telescope. Solved using Eloquent eager loading (e.g. using the with() method or lazy eager loading via load() to ensure records are fetched in a single select query).',
            ],
            [
                'question' => 'How would you structure your database and application code to handle strict tenant isolation in a SaaS application?',
                'category' => 'scenario',
                'difficulty' => 'hard',
                'suggested_answer' => 'By either using single-database multi-tenancy (scoping queries using a global TenantScope/BelongsToTenant trait that automatically applies tenant_id constraints) or multi-database segregation with dynamic connection switching.',
            ],
            [
                'question' => 'Tell us about a time you had a technical disagreement with a team lead or colleague. How did you approach and resolve it?',
                'category' => 'behavioral',
                'difficulty' => 'easy',
                'suggested_answer' => 'Approach through data, benchmarks, and objective proof. Construct a quick prototype/PoC to compare performance metrics or maintainability, discuss constructively, and defer to final lead decision if needed.',
            ],
            [
                'question' => 'Design a rate-limiting middleware that restricts API requests per client based on their subscription tier.',
                'category' => 'problem_solving',
                'difficulty' => 'hard',
                'suggested_answer' => 'Define a middleware that resolves the current tenant/user subscription tier, fetches their hourly rate limit, and utilizes Laravel RateLimiter facades to cache request counts based on IP or authenticated user ID.',
            ],
        ];
    }
}
