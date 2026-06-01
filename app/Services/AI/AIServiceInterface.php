<?php

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Parse raw resume text into structured candidate information.
     */
    public function parseResume(string $resumeText): array;

    /**
     * Compare parsed resume data against job requirements to calculate scores & recommendations.
     */
    public function screenCandidate(array $parsedResume, array $jobRequirement): array;

    /**
     * Generate customized interview questions based on candidate profile and job requirements.
     */
    public function generateInterviewQuestions(array $parsedResume, array $jobRequirement): array;
}
