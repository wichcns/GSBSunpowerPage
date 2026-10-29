<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_pipeline_states', function (Blueprint $table) {
            $table->id();
            $table->string('lead_source_id')->unique();
            $table->string('stage', 32);
            $table->unsignedTinyInteger('probability');
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('updated_by_user_id');
            $table->timestamps();
        });

        Schema::create('crm_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->string('lead_source_id')->index();
            $table->string('from_stage', 32)->nullable();
            $table->string('to_stage', 32);
            $table->unsignedTinyInteger('from_probability')->nullable();
            $table->unsignedTinyInteger('to_probability');
            $table->unsignedBigInteger('actor_user_id');
            $table->timestamps();
        });

        Schema::create('crm_engineering_projects', function (Blueprint $table) {
            $table->id();
            $table->string('lead_source_id')->unique();
            $table->string('project_code')->unique();
            $table->string('legacy_project_id')->nullable()->unique();
            $table->string('engineering_stage', 32)->default('NOT_STARTED');
            $table->string('sync_status', 32)->default('pending_connection');
            $table->string('external_project_id')->nullable();
            $table->string('origin', 16);
            $table->timestamps();
        });

        Schema::create('crm_integration_outbox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineering_project_id')->constrained('crm_engineering_projects');
            $table->string('event_type');
            $table->string('dedupe_key')->unique();
            $table->string('status', 32)->default('pending_connection');
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_integration_outbox');
        Schema::dropIfExists('crm_engineering_projects');
        Schema::dropIfExists('crm_stage_histories');
        Schema::dropIfExists('crm_pipeline_states');
    }
};
