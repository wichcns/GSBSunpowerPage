<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_engineering_survey_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineering_project_id')->unique()->constrained('crm_engineering_projects');
            $table->string('lead_source_id')->unique();
            $table->string('status', 20)->default('unassigned');
            $table->foreignId('assignee_user_id')->nullable()->constrained('crm_users');
            $table->dateTime('scheduled_at')->nullable();
            $table->text('result_summary')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('crm_users');
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('crm_engineering_survey_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_task_id')->constrained('crm_engineering_survey_tasks');
            $table->string('event_code', 32);
            $table->json('old_values')->nullable();
            $table->json('new_values');
            $table->foreignId('actor_user_id')->constrained('crm_users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_engineering_survey_histories');
        Schema::dropIfExists('crm_engineering_survey_tasks');
    }
};
