<?php

use App\Jobs\ProcessCandidateApplicationJob;
use App\Models\CandidateApplication;
use App\Models\Department;
use App\Models\JobBoardIntegration;
use App\Models\JobPost;
use App\Models\User;
use App\Services\AIInterviewService;
use App\Services\CandidateScoringService;
use App\Services\ResumeParserService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    // Seed department
    $this->department = Department::create([
        'name' => 'Engineering',
        'description' => 'Software engineering division',
    ]);

    // Create a job post
    $this->job = JobPost::create([
        'title' => 'Senior Laravel Developer',
        'department_id' => $this->department->id,
        'experience_required' => '5+ Years',
        'salary_range' => '₹12,00,000 - ₹18,00,000',
        'description' => 'We are seeking a senior backend software engineer with expertise in PHP, Laravel, MySQL, Docker, and REST APIs.',
        'status' => 'Active',
    ]);

    // Create an HR manager user
    $this->hr = User::create([
        'name' => 'HR Manager',
        'email' => 'hr@example.com',
        'password' => Hash::make('password'),
        'role' => 'hr_manager',
    ]);
});

test('public candidates can submit resumes and trigger immediate AI processing queue', function () {
    Queue::fake();
    Storage::fake('public');

    $file = UploadedFile::fake()->create('resume.docx', 100);

    $response = $this->post(route('careers.apply', $this->job), [
        'full_name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'contact_number' => '+91 9876543210',
        'resume_file' => $file,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('candidate_applications', [
        'full_name' => 'John Doe',
        'email' => 'john.doe@example.com',
    ]);

    $app = CandidateApplication::latest()->first();

    // Verify background AI queue job is pushed
    Queue::assertPushed(ProcessCandidateApplicationJob::class, function ($job) use ($app) {
        return $job->applicationId === $app->id;
    });
});

test('ProcessCandidateApplicationJob parses, scores, and generates questions successfully', function () {
    Storage::fake('public');

    // Save a mock file
    $mockFilePath = 'resumes/mock_resume.docx';
    Storage::disk('public')->put($mockFilePath, 'John Doe resume text. Experience: 5 years in PHP and Laravel.');

    $app = CandidateApplication::create([
        'job_post_id' => $this->job->id,
        'full_name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'contact_number' => '+91 9876543210',
        'resume_path' => $mockFilePath,
        'status' => 'Applied',
    ]);

    // Handle job synchronously
    $jobInstance = new ProcessCandidateApplicationJob($app->id, $app->tenant_id);

    $parser = new ResumeParserService;
    $scoring = new CandidateScoringService;
    $interview = new AIInterviewService;

    $jobInstance->handle($parser, $scoring, $interview);

    // Assert structured candidate data is populated
    $this->assertDatabaseHas('candidate_resume_data', [
        'candidate_application_id' => $app->id,
        'full_name' => 'John Doe',
    ]);

    // Assert match score is generated
    $this->assertDatabaseHas('candidate_match_scores', [
        'candidate_application_id' => $app->id,
    ]);

    // Assert interview questions are stored
    $this->assertDatabaseHas('ai_interview_questions', [
        'candidate_application_id' => $app->id,
    ]);

    // Assert logging was audited
    $this->assertDatabaseHas('resume_parse_logs', [
        'candidate_application_id' => $app->id,
        'status' => 'success',
    ]);
});

test('job feed endpoints serve dynamic xml json and rss listings', function () {
    // Trigger XML Feed
    $responseXml = $this->get(route('jobs.feed.xml'));
    $responseXml->assertStatus(200);
    $responseXml->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    // Trigger JSON Feed
    $responseJson = $this->get(route('jobs.feed.json'));
    $responseJson->assertStatus(200);
    $responseJson->assertJsonStructure([
        'version',
        'title',
        'items',
    ]);

    // Trigger RSS Feed
    $responseRss = $this->get(route('jobs.feed.rss'));
    $responseRss->assertStatus(200);
    $responseRss->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

    // Verify access telemetry is logged
    $this->assertDatabaseHas('job_feed_logs', [
        'feed_type' => 'xml',
    ]);
});

test('hr can manage job board integrations', function () {
    $this->actingAs($this->hr);

    // Save key
    $response = $this->post(route('jobs.integrations.save'), [
        'platform' => 'linkedin',
        'api_key' => 'test-key-123',
        'api_secret' => 'test-secret-456',
        'is_active' => '1',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('job_board_integrations', [
        'platform' => 'linkedin',
        'is_active' => true,
    ]);

    $integration = JobBoardIntegration::where('platform', 'linkedin')->first();
    expect($integration->api_key)->toEqual('test-key-123');
    expect($integration->api_secret)->toEqual('test-secret-456');
});

test('hr can view the ai ats check form', function () {
    $this->actingAs($this->hr);

    $response = $this->get(route('jobs.ats.check.form'));

    $response->assertSuccessful();
    $response->assertSee('AI ATS Resume Checker');
});

test('hr can submit a resume for immediate synchronous ai ats check', function () {
    $this->actingAs($this->hr);
    Storage::fake('public');

    $file = UploadedFile::fake()->create('resume_test.docx', 100);

    $response = $this->post(route('jobs.ats.check.process'), [
        'job_post_id' => $this->job->id,
        'full_name' => 'Alice Smith',
        'email' => 'alice.smith@example.com',
        'contact_number' => '+91 9876500000',
        'resume_file' => $file,
    ]);

    // Should redirect to candidate AI evaluation page
    $app = CandidateApplication::where('email', 'alice.smith@example.com')->first();
    expect($app)->not->toBeNull();

    $response->assertRedirect(route('jobs.candidate.ai', $app->id));

    // Verify all services ran synchronously and structured resume data exists
    $this->assertDatabaseHas('candidate_resume_data', [
        'candidate_application_id' => $app->id,
        'full_name' => 'Alice Smith',
    ]);

    $this->assertDatabaseHas('candidate_match_scores', [
        'candidate_application_id' => $app->id,
    ]);

    $this->assertDatabaseHas('ai_interview_questions', [
        'candidate_application_id' => $app->id,
    ]);

    $this->assertDatabaseHas('resume_parse_logs', [
        'candidate_application_id' => $app->id,
        'status' => 'success',
    ]);
});
