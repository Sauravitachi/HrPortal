<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Jobs\PublishJobPostJob;
use App\Models\CandidateApplication;
use App\Models\JobBoardIntegration;
use App\Models\JobPost;
use App\Models\JobPublishing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                return back()->with('error', 'Invalid JSON structure in Settings field.');
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

        return back()->with('success', 'Integration settings saved successfully.');
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
}
