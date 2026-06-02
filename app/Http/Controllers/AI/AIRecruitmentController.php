<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Jobs\PublishJobPostJob;
use App\Models\CandidateApplication;
use App\Models\JobBoardIntegration;
use App\Models\JobPost;
use App\Models\JobPublishing;
use App\Models\ResumeParseLog;
use App\Services\AIInterviewService;
use App\Services\CandidateScoringService;
use App\Services\ResumeParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AIRecruitmentController extends Controller
{
    /**
     * Display the AI Recruitment Dashboard showing analytics & top candidates.
     */
    public function dashboard(): View
    {
        $tenantId = app('currentTenant')->id ?? null;

        // Fetch jobs with application count
        $jobs = JobPost::withCount('applications')->latest()->get();

        // Fetch top candidates sorted by Match Score
        $topCandidates = CandidateApplication::with(['jobPost.department', 'matchScore'])
            ->whereHas('matchScore', function ($query) {
                $query->where('match_score', '>=', 70);
            })
            ->latest()
            ->take(10)
            ->get();

        // Group candidate counts by match categories
        $excellentCount = CandidateApplication::whereHas('matchScore', fn ($q) => $q->where('match_score', '>=', 90))->count();
        $strongCount = CandidateApplication::whereHas('matchScore', fn ($q) => $q->whereBetween('match_score', [80, 89]))->count();
        $moderateCount = CandidateApplication::whereHas('matchScore', fn ($q) => $q->whereBetween('match_score', [60, 79]))->count();
        $weakCount = CandidateApplication::whereHas('matchScore', fn ($q) => $q->where('match_score', '<', 60))->count();

        // Fetch integration statuses
        $integrations = JobBoardIntegration::all()->keyBy('platform');
        $platforms = ['linkedin', 'indeed', 'glassdoor', 'foundit', 'naukri'];

        // Fetch recent publishing logs
        $publishings = JobPublishing::with('jobPost')->latest()->take(10)->get();

        return view('recruitment.dashboard', compact(
            'jobs',
            'topCandidates',
            'excellentCount',
            'strongCount',
            'moderateCount',
            'weakCount',
            'platforms',
            'integrations',
            'publishings'
        ));
    }

    /**
     * Show detailed AI Candidate Screening Report.
     */
    public function candidateReport(CandidateApplication $application): View
    {
        $application->load([
            'jobPost.department',
            'matchScore',
            'resumeData',
            'skills',
            'education',
            'projects',
            'generatedQuestions',
        ]);

        if (! $application->resumeData) {
            return view('recruitment.candidate_ai', [
                'application' => $application,
                'error' => 'AI screening data has not been processed for this candidate yet.',
            ]);
        }

        return view('recruitment.candidate_ai', compact('application'));
    }

    /**
     * Display Job Board Integration configurations.
     */
    public function integrations(): View
    {
        $integrations = JobBoardIntegration::all()->keyBy('platform');
        $platforms = [
            'linkedin' => 'LinkedIn',
            'indeed' => 'Indeed',
            'glassdoor' => 'Glassdoor',
            'foundit' => 'Foundit (Monster)',
            'naukri' => 'Naukri',
        ];

        return view('recruitment.integrations', compact('integrations', 'platforms'));
    }

    /**
     * Save/Update Job Board credentials.
     */
    public function saveIntegration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'in:linkedin,indeed,glassdoor,foundit,naukri'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'settings_json' => ['nullable', 'string'],
        ]);

        $settings = null;
        if (! empty($validated['settings_json'])) {
            $settings = json_decode($validated['settings_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->route('jobs.integrations')->with('error', 'Invalid JSON structure in Settings field.');
            }
        }

        $tenantId = app('currentTenant')->id;

        JobBoardIntegration::updateOrCreate(
            ['tenant_id' => $tenantId, 'platform' => $validated['platform']],
            [
                'api_key' => $validated['api_key'],
                'api_secret' => $validated['api_secret'],
                'is_active' => $request->has('is_active'),
                'settings' => $settings,
            ]
        );

        return redirect()->route('jobs.integrations')->with('success', 'Integration settings saved successfully.');
    }

    /**
     * Publish job post to multiple platforms manually.
     */
    public function publishJob(Request $request, JobPost $job): RedirectResponse
    {
        $request->validate([
            'platforms' => ['required', 'array'],
            'platforms.*' => ['string', 'in:linkedin,indeed,glassdoor,foundit,naukri'],
        ]);

        $platforms = $request->input('platforms');
        $tenantId = app('currentTenant')->id ?? null;

        // Dispatch background job
        PublishJobPostJob::dispatch($job->id, $platforms, $tenantId);

        return back()->with('success', 'Job publishing dispatched to queue. Platforms: '.implode(', ', array_map('ucfirst', $platforms)));
    }

    /**
     * Display the AI ATS Check Form.
     */
    public function atsCheckForm(): View
    {
        // Get active job posts for selection
        $jobs = JobPost::where('status', 'Active')->latest()->get();

        return view('recruitment.ats_check', compact('jobs'));
    }

    /**
     * Process AI ATS Check on uploaded resume.
     */
    public function atsCheckProcess(
        Request $request,
        ResumeParserService $parserService,
        CandidateScoringService $scoringService,
        AIInterviewService $interviewService
    ): RedirectResponse {
        $validated = $request->validate([
            'job_post_id' => ['required', 'exists:job_posts,id'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'resume_file' => ['required', 'file', 'mimes:pdf,docx', 'max:5120'],
        ]);

        $job = JobPost::findOrFail($validated['job_post_id']);
        $tenantId = app('currentTenant')->id ?? null;

        // Store resume
        $resumePath = $request->file('resume_file')->store('resumes', 'public');

        // Create a CandidateApplication
        $application = CandidateApplication::create([
            'tenant_id' => $tenantId,
            'job_post_id' => $job->id,
            'full_name' => $validated['full_name'] ?? 'ATS Check Candidate',
            'email' => $validated['email'] ?? 'ats-check@example.com',
            'contact_number' => $validated['contact_number'] ?? '0000000000',
            'resume_path' => $resumePath,
            'status' => 'Screening',
        ]);

        try {
            // Process synchronously
            $parserService->parse($application);
            $scoringService->score($application, $job);
            $interviewService->generateQuestions($application, $job);

            // Audit Log
            ResumeParseLog::create([
                'tenant_id' => $tenantId,
                'candidate_application_id' => $application->id,
                'status' => 'success',
            ]);

            // Set shortlisted if score >= 75
            $score = $application->matchScore()->first();
            if ($score && $score->match_score >= 75) {
                $application->update(['status' => 'Shortlisted']);
            } else {
                $application->update(['status' => 'Applied']);
            }

            return redirect()->route('jobs.candidate.ai', $application->id)
                ->with('success', 'AI ATS check completed successfully! Performance scorecard and interview guide generated.');
        } catch (\Exception $e) {
            Log::error('AI ATS Check Failed: '.$e->getMessage(), [
                'exception' => $e,
                'application_id' => $application->id,
            ]);

            ResumeParseLog::create([
                'tenant_id' => $tenantId,
                'candidate_application_id' => $application->id,
                'status' => 'failed',
                'error_message' => mb_scrub($e->getMessage(), 'UTF-8'),
            ]);

            $application->update(['status' => 'Applied']);

            return back()->with('error', 'AI ATS check encountered an error: '.$e->getMessage());
        }
    }
}
