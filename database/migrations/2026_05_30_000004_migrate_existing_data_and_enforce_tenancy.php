<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $businessTables = [
        'users', 'employees', 'departments', 'designations', 'attendance',
        'employee_documents', 'leave_types', 'holidays', 'leave_requests',
        'job_posts', 'payrolls', 'activity_logs', 'candidate_applications', 'interviews',
    ];

    public function up(): void
    {
        // 1. Create a Default Tenant & Company if none exists
        $tenantId = DB::table('tenants')->where('subdomain', 'default')->value('id');

        if (! $tenantId) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Default Company',
                'subdomain' => 'default',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('companies')->insert([
                'tenant_id' => $tenantId,
                'name' => 'Default Company',
                'subscription_plan' => 'premium', // Grant full premium features to existing records
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Assign all existing data to the Default Tenant
        foreach ($this->businessTables as $tableName) {
            DB::table($tableName)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }

        // 3. Make tenant_id NOT NULL for all business tables (except users, since Super Admins have null tenant_id)
        foreach ($this->businessTables as $tableName) {
            if ($tableName !== 'users') {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                });
            }
        }

        // 4. Update unique constraints to be scoped per tenant
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->unique(['tenant_id', 'email']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_employee_id_unique');
            $table->dropUnique('employees_email_unique');
            $table->unique(['tenant_id', 'employee_id']);
            $table->unique(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'employee_id']);
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique('employee_id');
            $table->unique('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique('email');
        });

        foreach ($this->businessTables as $tableName) {
            if ($tableName !== 'users') {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable(true)->change();
                });
            }
        }
    }
};
