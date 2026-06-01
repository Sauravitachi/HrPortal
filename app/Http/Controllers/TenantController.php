<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Display a listing of all tenants and companies.
     */
    public function index(): View
    {
        // Super Admins see all tenants across the landlord boundary, with their associated users and employees
        $tenants = Tenant::with([
            'company',
            'users',
            'employees.department',
            'employees.designation',
        ])->latest()->get();

        return view('admin.tenants', compact('tenants'));
    }

    /**
     * Store a newly created tenant and company profile in the system.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'alpha_dash', 'max:255', 'unique:tenants,subdomain'],
            'subscription_plan' => ['required', 'string', 'in:free,basic,premium'],
        ]);

        // 1. Create the tenant context
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'subdomain' => strtolower($validated['subdomain']),
        ]);

        // 2. Create the associated company profile
        Company::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'subscription_plan' => $validated['subscription_plan'],
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'New tenant and company registered successfully from UI!');
    }

    /**
     * Update the subscription plan for a tenant.
     */
    public function updatePlan(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'subscription_plan' => ['required', 'string', 'in:free,basic,premium'],
        ]);

        $company = $tenant->company;
        if ($company) {
            $company->update([
                'subscription_plan' => $request->input('subscription_plan'),
            ]);
        }

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant subscription updated successfully!');
    }

    /**
     * Delete a tenant and all their associated data from the system.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        if ($tenant->subdomain === 'default') {
            return back()->with('error', 'Cannot delete the system default primary tenant.');
        }

        $tenant->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant and scoped records removed successfully!');
    }
}
