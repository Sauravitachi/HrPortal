<?php

namespace App\Jobs;

use App\Models\CandidateApplication;
use App\Models\ResumeParseLog;
use App\Models\Tenant;
use App\Services\AIInterviewService;
use App\Services\CandidateScoringService;
use App\Services\ResumeParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCandidateApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60; // 60 seconds retry backoff

    public function __construct(
        public int $applicationId,
        public ?int $tenantId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ResumeParserService $parserService,
        CandidateScoringService $scoringService,
        AIInterviewService $interviewService
    ): void {
        // Establish tenant boundaries if context is not currently bound
        if ($this->tenantId) {
            $tenant = Tenant::find($this->tenantId);
            if ($tenant) {
                $tenant->makeCurrent();
            }
        }

        $application = CandidateApplication::with('jobPost')->find($this->applicationId);
        if (! $application) {
            Log::error("ProcessCandidateApplicationJob: Candidate Application #{$this->applicationId} not found.");

            return;
        }

        // Set status to Screening while processing
        $application->update(['status' => 'Screening']);

        try {
            // 1. Parser: convert resume upload to structured database models
            $parserService->parse($application);

            // 2. Assessor: score candidate profile against job requisition
            $scoringService->score($application, $application->jobPost);

            // 3. Interview: generate customized questions & rubrics
            $interviewService->generateQuestions($application, $application->jobPost);

            // 4. Log transaction audit success
            ResumeParseLog::create([
                'tenant_id' => $application->tenant_id,
                'candidate_application_id' => $application->id,
                'status' => 'success',
            ]);

            // Set final status as Shortlisted or Applied depending on score (or keep Screening)
            // Let's set it as Shortlisted if match score is high, or just keep status updated
            $score = $application->matchScore()->first();
            if ($score && $score->match_score >= 75) {
                $application->update(['status' => 'Shortlisted']);
            }
        } catch (\Exception $e) {
            Log::error('ProcessCandidateApplicationJob Failed: '.$e->getMessage(), [
                'exception' => $e,
                'application_id' => $this->applicationId,
            ]);

            ResumeParseLog::create([
                'tenant_id' => $application->tenant_id,
                'candidate_application_id' => $application->id,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $application->update(['status' => 'Applied']); // Rollback status
            throw $e; // Rethrow to trigger queue retry
        }
    }
}
