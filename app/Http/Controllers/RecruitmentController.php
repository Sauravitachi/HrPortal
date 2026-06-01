<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCandidateApplicationJob;
use App\Jobs\PublishJobPostJob;
use App\Models\ActivityLog;
use App\Models\CandidateApplication;
use App\Models\Department;
use App\Models\Interview;
use App\Models\JobBoardIntegration;
use App\Models\JobCategory;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    /**
     * Display recruitment listings and candidates pipeline for HR Admins.
     */
    public function index(Request $request): View
    {
        $jobs = JobPost::with(['department', 'jobCategory'])->withCount('applications')->get();
        $selectedJobId = $request->input('job_post_id');

        $query = CandidateApplication::with(['jobPost.department']);

        if ($selectedJobId) {
            $query->where('job_post_id', $selectedJobId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $applications = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::all();
        $categories = JobCategory::all();

        return view('recruitment.index', compact('jobs', 'applications', 'selectedJobId', 'departments', 'categories'));
    }

    /**
     * Store a newly created job post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'job_category_id' => ['nullable', 'exists:job_categories,id'],
            'experience_required' => ['required', 'string', 'max:100'],
            'salary_range' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
        ]);

        $job = JobPost::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Job Post Created',
            'description' => "Created job post for {$job->title} in department.",
        ]);

        // Auto-syndicate to active external platforms
        $activePlatforms = JobBoardIntegration::where('is_active', true)->pluck('platform')->toArray();
        if (! empty($activePlatforms)) {
            PublishJobPostJob::dispatch($job->id, $activePlatforms, $job->tenant_id);
        }

        return redirect()->route('jobs.index')->with('success', 'Job posting created successfully.');
    }

    /**
     * Display the public careers portal listing active jobs.
     */
    public function careersPortal(): View
    {
        $jobs = JobPost::with(['department', 'jobCategory'])
            ->where('status', 'Active')
            ->latest()
            ->get();

        $categories = JobCategory::all();

        return view('recruitment.careers', compact('jobs', 'categories'));
    }

    /**
     * Display a single job posting for public application.
     */
    public function careersJob(JobPost $job): View
    {
        if ($job->status !== 'Active') {
            abort(404);
        }

        return view('recruitment.careers_show', compact('job'));
    }

    /**
     * Handle public job application submissions.
     */
    public function apply(Request $request, JobPost $job): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'contact_number' => ['required', 'string', 'max:20'],
            'resume_file' => ['required', 'file', 'mimes:pdf,docx', 'max:5120'],
        ]);

        $resumePath = $request->file('resume_file')->store('resumes', 'public');

        $application = CandidateApplication::create([
            'job_post_id' => $job->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'resume_path' => $resumePath,
            'status' => 'Applied',
        ]);

        // Dispatch background processing job
        ProcessCandidateApplicationJob::dispatch(
            $application->id,
            $application->tenant_id
        );

        return back()->with('success', 'Your application has been submitted successfully.');
    }

    /**
     * Update candidate status in the ATS tracking pipeline.
     */
    public function updateStatus(Request $request, CandidateApplication $application): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:Applied,Screening,Shortlisted,Interview Scheduled,Interviewed,Selected,Rejected,Hired'],
        ]);

        $oldStatus = $application->status;
        $newStatus = $request->input('status');
        $application->status = $newStatus;
        $application->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Candidate Status Updated',
            'description' => "Updated status of {$application->full_name} for {$application->jobPost->title} from {$oldStatus} to {$newStatus}.",
        ]);

        return back()->with('success', 'Candidate status updated successfully.');
    }

    /**
     * Schedule an interview.
     */
    public function scheduleInterview(Request $request, CandidateApplication $application): RedirectResponse
    {
        $request->validate([
            'interview_date' => ['required', 'date', 'after:now'],
            'interview_panel' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        Interview::create([
            'candidate_application_id' => $application->id,
            'interview_date' => $request->input('interview_date'),
            'interview_panel' => $request->input('interview_panel'),
            'notes' => $request->input('notes'),
            'status' => 'Pending',
        ]);

        $application->update(['status' => 'Interview Scheduled']);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Interview Scheduled',
            'description' => "Scheduled interview for {$application->full_name} on {$request->input('interview_date')}.",
        ]);

        return back()->with('success', 'Interview scheduled and candidate status updated successfully.');
    }
}
