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
        Schema::create('candidate_resume_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('candidate_application_id')->constrained()->cascadeOnDelete();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->decimal('total_experience_years', 4, 1)->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_designation')->nullable();
            $table->longText('raw_text')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('candidate_application_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->string('skill_type')->default('technical'); // technical, soft
            $table->timestamps();
        });

        Schema::create('candidate_education', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('candidate_application_id')->constrained()->cascadeOnDelete();
            $table->string('degree');
            $table->string('college');
            $table->integer('passing_year')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('candidate_application_id')->constrained()->cascadeOnDelete();
            $table->string('project_name');
            $table->text('technologies_used')->nullable(); // String list or JSON
            $table->timestamps();
        });

        Schema::create('ai_interview_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('candidate_application_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->string('category'); // technical, behavioral, scenario, problem_solving
            $table->string('difficulty'); // easy, medium, hard
            $table->text('suggested_answer')->nullable();
            $table->timestamps();
        });

        Schema::create('job_board_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('platform'); // linkedin, indeed, glassdoor, foundit, naukri
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('job_publishings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('status')->default('pending'); // pending, published, failed
            $table->text('error_message')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_feed_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('feed_type'); // xml, json, rss
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('resume_parse_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('candidate_application_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // success, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_parse_logs');
        Schema::dropIfExists('job_feed_logs');
        Schema::dropIfExists('job_publishings');
        Schema::dropIfExists('job_board_integrations');
        Schema::dropIfExists('ai_interview_questions');
        Schema::dropIfExists('candidate_projects');
        Schema::dropIfExists('candidate_education');
        Schema::dropIfExists('candidate_skills');
        Schema::dropIfExists('candidate_resume_data');
    }
};
