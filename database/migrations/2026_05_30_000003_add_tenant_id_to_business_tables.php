<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        foreach ($this->businessTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->index()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->businessTables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
