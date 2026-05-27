<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_id')->unique();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('contact_number');
            $table->string('profile_image')->nullable();
            $table->string('gender');
            $table->date('date_of_birth');
            $table->text('address');
            $table->string('emergency_contact');
            $table->string('blood_group');
            $table->date('joining_date');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('designation_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('reporting_manager_id')->nullable();
            $table->string('employee_type'); // Full-time, Part-time, Contract, Intern
            $table->string('work_location'); // Onsite, Remote, Hybrid
            $table->string('employment_status')->default('Active'); // Active, Inactive, Terminated
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('hra', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
