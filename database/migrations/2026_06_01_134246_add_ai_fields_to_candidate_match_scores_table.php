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
        Schema::table('candidate_match_scores', function (Blueprint $table) {
            $table->json('strengths')->nullable()->after('analysis_summary');
            $table->json('missing_skills')->nullable()->after('strengths');
            $table->text('experience_gap')->nullable()->after('missing_skills');
            $table->string('hiring_recommendation')->nullable()->after('experience_gap');
            $table->json('evaluation_scorecard')->nullable()->after('hiring_recommendation');
            $table->json('feedback_form')->nullable()->after('evaluation_scorecard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_match_scores', function (Blueprint $table) {
            $table->dropColumn([
                'strengths',
                'missing_skills',
                'experience_gap',
                'hiring_recommendation',
                'evaluation_scorecard',
                'feedback_form',
            ]);
        });
    }
};
