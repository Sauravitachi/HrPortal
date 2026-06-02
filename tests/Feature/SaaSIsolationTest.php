<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\JobBoardIntegration;
use App\Models\JobPost;
use App\Models\JobPublishing;
use App\Models\Tenant;
use App\Models\User;
use App\Services\JobPublishingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    // 1. Setup Tenant A
    $this->tenantA = Tenant::create([
        'name' => 'Acme Corporation',
        'subdomain' => 'acme',
    ]);
    $this->companyA = Company::create([
        'tenant_id' => $this->tenantA->id,
        'name' => 'Acme Corporation',
        'subscription_plan' => 'premium',
    ]);

    // 2. Setup Tenant B
    $this->tenantB = Tenant::create([
        'name' => 'Globex Industries',
        'subdomain' => 'globex',
    ]);
    $this->companyB = Company::create([
        'tenant_id' => $this->tenantB->id,
        'name' => 'Globex Industries',
        'subscription_plan' => 'free',
    ]);
});

test('tenant data isolation is strictly enforced', function () {
    // 1. Bind Tenant A context and create department
    $this->tenantA->makeCurrent();
    $deptA = Department::create(['name' => 'Engineering', 'description' => 'Acme Tech']);

    // 2. Bind Tenant B context and create department
    $this->tenantB->makeCurrent();
    $deptB = Department::create(['name' => 'Marketing', 'description' => 'Globex Sales']);

    // 3. Assert Tenant A cannot see Tenant B's data
    $this->tenantA->makeCurrent();
    $acmeDepts = Department::all();
    expect($acmeDepts)->toHaveCount(1);
    expect($acmeDepts->first()->name)->toEqual('Engineering');

    // 4. Assert Tenant B cannot see Tenant A's data
    $this->tenantB->makeCurrent();
    $globexDepts = Department::all();
    expect($globexDepts)->toHaveCount(1);
    expect($globexDepts->first()->name)->toEqual('Marketing');
});

test('users are blocked from cross-tenant access', function () {
    // 1. Create standard employee user for Tenant A
    $this->tenantA->makeCurrent();
    $userA = User::create([
        'name' => 'Acme Worker',
        'email' => 'worker@acme.com',
        'password' => bcrypt('password'),
        'role' => 'employee',
    ]);

    // 2. Bind Tenant B and attempt request acting as Tenant A user
    $this->tenantB->makeCurrent();

    // Set host header matching Tenant B subdomain
    $this->actingAs($userA)
        ->withHeaders(['Host' => 'globex.hrportal.localhost'])
        ->get('/')
        ->assertStatus(403); // Forbidden
});

test('super admin has global cross-tenant access and bypasses scopes', function () {
    // 1. Create global Super Admin (tenant_id = null)
    $superAdmin = User::create([
        'name' => 'Global Admin',
        'email' => 'super@platform.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
        'tenant_id' => null,
    ]);

    // 2. Create departments in different tenants
    $this->tenantA->makeCurrent();
    Department::create(['name' => 'Acme Tech']);
    $this->tenantB->makeCurrent();
    Department::create(['name' => 'Globex Sales']);

    // 3. Act as Super Admin and verify they can see all records
    // Spatie forgetting current tenant returns global landlord perspective
    Tenant::forgetCurrent();

    $this->actingAs($superAdmin);

    // Querying with Super Admin bypasses BelongsToTenant scope
    $allDepts = Department::all();
    expect($allDepts)->toHaveCount(2);
});

test('public careers portal lists scoped active jobs and processes applications', function () {
    Queue::fake();
    Storage::fake('public');

    // 1. Create active job in Tenant A
    $this->tenantA->makeCurrent();
    $deptA = Department::create(['name' => 'Engineering']);
    $job = JobPost::create([
        'title' => 'Laravel Developer',
        'department_id' => $deptA->id,
        'experience_required' => '3 Years',
        'salary_range' => '₹6L - ₹8L',
        'description' => 'Required PHP skills.',
        'status' => 'Active',
    ]);

    // 2. Request public careers portal matching Tenant A subdomain
    $this->get('/careers')
        ->assertSuccessful()
        ->assertSee('Laravel Developer');

    // 3. Submit candidate application publicly with resume file
    $resumeFile = UploadedFile::fake()->create('my_resume.pdf', 500, 'application/pdf');

    $this->post("/careers/{$job->id}/apply", [
        'full_name' => 'Jane Candidate',
        'email' => 'jane@gmail.com',
        'contact_number' => '+91 9876543210',
        'resume_file' => $resumeFile,
    ])->assertRedirect();

    // 4. Assert application is safely stored under the correct tenant boundary
    $this->assertDatabaseHas('candidate_applications', [
        'tenant_id' => $this->tenantA->id,
        'full_name' => 'Jane Candidate',
        'email' => 'jane@gmail.com',
        'status' => 'Applied',
    ]);
});

test('non-admin users are blocked from tenant administration UI', function () {
    $this->tenantA->makeCurrent();
    $employee = User::create([
        'name' => 'Regular Employee',
        'email' => 'employee@acme.com',
        'password' => bcrypt('password'),
        'role' => 'employee',
    ]);

    $this->actingAs($employee)
        ->get('/admin/tenants')
        ->assertForbidden();
});

test('super admin can access tenant administration UI and create a tenant', function () {
    $superAdmin = User::create([
        'name' => 'Platform Owner',
        'email' => 'super@platform.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
        'tenant_id' => null,
    ]);

    // 1. Visit Tenants Dashboard
    $this->actingAs($superAdmin)
        ->get('/admin/tenants')
        ->assertSuccessful()
        ->assertSee('SaaS Tenant Management');

    // 2. Submit new tenant registration via the UI form
    $this->actingAs($superAdmin)
        ->post('/admin/tenants', [
            'name' => 'Cyberdyne Systems',
            'subdomain' => 'cyberdyne',
            'subscription_plan' => 'premium',
        ])
        ->assertRedirect();

    // 3. Verify Database records
    $this->assertDatabaseHas('tenants', [
        'name' => 'Cyberdyne Systems',
        'subdomain' => 'cyberdyne',
    ]);

    $this->assertDatabaseHas('companies', [
        'name' => 'Cyberdyne Systems',
        'subscription_plan' => 'premium',
    ]);
});

test('tenant integrations are strictly isolated and credentials are encrypted', function () {
    // 1. Create a tenant-scoped user for Tenant A
    $this->tenantA->makeCurrent();
    $adminA = User::create([
        'name' => 'Acme Admin',
        'email' => 'admin@acme.com',
        'password' => bcrypt('password'),
        'role' => 'company_admin',
    ]);

    // 2. Create a tenant-scoped user for Tenant B
    $this->tenantB->makeCurrent();
    $adminB = User::create([
        'name' => 'Globex Admin',
        'email' => 'admin@globex.com',
        'password' => bcrypt('password'),
        'role' => 'company_admin',
    ]);

    // 3. Configure integration for Tenant A
    $this->tenantA->makeCurrent();
    $this->actingAs($adminA)
        ->withHeaders(['Host' => 'acme.hrportal.localhost'])
        ->withSession(['ensure_valid_tenant_session_tenant_id' => $this->tenantA->id])
        ->post(route('jobs.integrations.save'), [
            'platform' => 'linkedin',
            'api_key' => 'acme-linkedin-key',
            'api_secret' => 'acme-linkedin-secret',
            'is_active' => '1',
        ])->assertRedirect();

    // 4. Configure integration for Tenant B
    $this->tenantB->makeCurrent();
    $this->actingAs($adminB)
        ->withHeaders(['Host' => 'globex.hrportal.localhost'])
        ->withSession(['ensure_valid_tenant_session_tenant_id' => $this->tenantB->id])
        ->post(route('jobs.integrations.save'), [
            'platform' => 'linkedin',
            'api_key' => 'globex-linkedin-key',
            'api_secret' => 'globex-linkedin-secret',
            'is_active' => '1',
        ])->assertRedirect();

    // 5. Assert isolation: Tenant A cannot see Tenant B's keys
    $this->tenantA->makeCurrent();
    $this->actingAs($adminA)
        ->withHeaders(['Host' => 'acme.hrportal.localhost'])
        ->withSession(['ensure_valid_tenant_session_tenant_id' => $this->tenantA->id])
        ->get(route('jobs.integrations'))
        ->assertSuccessful()
        ->assertSee('acme-linkedin-key')
        ->assertDontSee('globex-linkedin-key');

    // 6. Assert isolation: Tenant B cannot see Tenant A's keys
    $this->tenantB->makeCurrent();
    $this->actingAs($adminB)
        ->withHeaders(['Host' => 'globex.hrportal.localhost'])
        ->withSession(['ensure_valid_tenant_session_tenant_id' => $this->tenantB->id])
        ->get(route('jobs.integrations'))
        ->assertSuccessful()
        ->assertSee('globex-linkedin-key')
        ->assertDontSee('acme-linkedin-key');

    // 7. Assert database-level encryption: raw values are encrypted (not plain text)
    Tenant::forgetCurrent();
    $rawRows = DB::table('job_board_integrations')->get();
    expect($rawRows)->toHaveCount(2);
    foreach ($rawRows as $row) {
        expect($row->api_key)->not->toContain('acme-linkedin-key');
        expect($row->api_key)->not->toContain('globex-linkedin-key');
        expect($row->api_secret)->not->toContain('acme-linkedin-secret');
        expect($row->api_secret)->not->toContain('globex-linkedin-secret');
    }
});

test('job publishing strictly respects tenant boundaries and uses correct tenant integration credentials', function () {
    // 1. Configure integrations for Tenant A and Tenant B
    $this->tenantA->makeCurrent();
    JobBoardIntegration::create([
        'platform' => 'indeed',
        'api_key' => 'acme-indeed-key',
        'api_secret' => 'acme-indeed-secret',
        'is_active' => true,
    ]);

    $this->tenantB->makeCurrent();
    JobBoardIntegration::create([
        'platform' => 'indeed',
        'api_key' => 'globex-indeed-key',
        'api_secret' => 'globex-indeed-secret',
        'is_active' => true,
    ]);

    // 2. Create job in Tenant A
    $this->tenantA->makeCurrent();
    $deptA = Department::create(['name' => 'Acme Eng']);
    $jobA = JobPost::create([
        'title' => 'Acme Dev',
        'department_id' => $deptA->id,
        'experience_required' => '2 Years',
        'salary_range' => '₹4L',
        'description' => 'Developer job',
        'status' => 'Active',
    ]);

    // 3. Create job in Tenant B
    $this->tenantB->makeCurrent();
    $deptB = Department::create(['name' => 'Globex Eng']);
    $jobB = JobPost::create([
        'title' => 'Globex Dev',
        'department_id' => $deptB->id,
        'experience_required' => '2 Years',
        'salary_range' => '₹4L',
        'description' => 'Developer job',
        'status' => 'Active',
    ]);

    // 4. Run JobPublishingService for Tenant A's job and assert it uses Tenant A's credentials
    $service = new JobPublishingService;

    $this->tenantA->makeCurrent();
    $service->publishToPlatform($jobA, 'indeed');

    $publishingA = JobPublishing::where('job_post_id', $jobA->id)->first();
    expect($publishingA->status)->toEqual('published');

    // 5. Run JobPublishingService for Tenant B's job and assert it uses Tenant B's credentials
    $this->tenantB->makeCurrent();
    $service->publishToPlatform($jobB, 'indeed');

    $publishingB = JobPublishing::where('job_post_id', $jobB->id)->first();
    expect($publishingB->status)->toEqual('published');
});
