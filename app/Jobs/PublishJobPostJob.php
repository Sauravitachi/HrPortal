<?php

namespace App\Jobs;

use App\Models\JobPost;
use App\Models\Tenant;
use App\Services\JobPublishingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishJobPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 120; // 2 minutes retry backoff

    public function __construct(
        public int $jobId,
        public array $platforms,
        public ?int $tenantId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(JobPublishingService $publishingService): void
    {
        // Establish tenant boundaries if context is not currently bound
        if ($this->tenantId) {
            $tenant = Tenant::find($this->tenantId);
            if ($tenant) {
                $tenant->makeCurrent();
            }
        }

        $job = JobPost::find($this->jobId);
        if (! $job) {
            Log::error("PublishJobPostJob: Job Post #{$this->jobId} not found.");

            return;
        }

        try {
            $publishingService->publish($job, $this->platforms);
        } catch (\Exception $e) {
            Log::error('PublishJobPostJob Failed: '.$e->getMessage(), [
                'exception' => $e,
                'job_id' => $this->jobId,
                'platforms' => $this->platforms,
            ]);
            throw $e; // Rethrow to trigger queue retry
        }
    }
}
