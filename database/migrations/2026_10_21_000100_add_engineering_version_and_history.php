<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_engineering_projects', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
        });

        Schema::create('crm_engineering_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineering_project_id')->constrained('crm_engineering_projects');
            $table->string('lead_source_id')->index();
            $table->string('from_stage', 32);
            $table->string('to_stage', 32);
            $table->unsignedBigInteger('actor_user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_engineering_stage_histories');
        Schema::table('crm_engineering_projects', function (Blueprint $table) {
            $table->dropColumn(['version', 'updated_by_user_id']);
        });
    }
};
