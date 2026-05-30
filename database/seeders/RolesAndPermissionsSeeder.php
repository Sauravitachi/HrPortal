<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Resolve current tenant team ID context
        $tenant = Tenant::where('subdomain', 'default')->first();
        if ($tenant) {
            setPermissionsTeamId($tenant->id);
        }

        // 2. Define standard system permissions
        $permissions = [
            // Core SaaS
            'manage tenants',
            'view platform analytics',
            // Company Admin
            'manage company users',
            'manage settings',
            'view billing',
            // HR Manager
            'manage employees',
            'approve leaves',
            'manage recruitment',
            'schedule interviews',
            'generate payroll',
            // Employee
            'view personal dashboard',
            'submit leave request',
            'check in',
            'check out',
            'view salary slips',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 3. Create scoped roles and attach corresponding permissions

        // Employee Role
        $employeeRole = Role::findOrCreate('employee', 'web');
        $employeeRole->givePermissionTo([
            'view personal dashboard',
            'submit leave request',
            'check in',
            'check out',
            'view salary slips',
        ]);

        // HR Manager Role
        $hrManagerRole = Role::findOrCreate('hr_manager', 'web');
        $hrManagerRole->givePermissionTo([
            'view personal dashboard',
            'submit leave request',
            'check in',
            'check out',
            'view salary slips',
            'manage employees',
            'approve leaves',
            'manage recruitment',
            'schedule interviews',
            'generate payroll',
        ]);

        // Company Admin Role
        $companyAdminRole = Role::findOrCreate('company_admin', 'web');
        $companyAdminRole->givePermissionTo([
            'view personal dashboard',
            'submit leave request',
            'check in',
            'check out',
            'view salary slips',
            'manage employees',
            'approve leaves',
            'manage recruitment',
            'schedule interviews',
            'generate payroll',
            'manage company users',
            'manage settings',
            'view billing',
        ]);

        // Super Admin Role (optional, as they bypass Gate)
        Role::findOrCreate('super_admin', 'web');
    }
}
