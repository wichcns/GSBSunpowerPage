<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_engineering_construction_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineering_project_id')->unique('crm_construction_project_uq');
            $table->foreign('engineering_project_id', 'crm_construction_project_fk')->references('id')->on('crm_engineering_projects');
            $table->string('lead_source_id')->unique();
            $table->foreignId('owner_user_id')->constrained('crm_users');
            $table->date('started_on');
            $table->date('target_finish_on')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
        Schema::create('crm_engineering_construction_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('construction_record_id');
            $table->foreign('construction_record_id', 'crm_construction_update_record_fk')->references('id')->on('crm_engineering_construction_records');
            $table->text('note');
            $table->foreignId('actor_user_id')->constrained('crm_users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_engineering_construction_updates');
        Schema::dropIfExists('crm_engineering_construction_records');
    }
};
