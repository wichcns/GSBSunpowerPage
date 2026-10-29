<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_lead_details', function (Blueprint $table) {
            $table->id();
            $table->string('lead_source_id')->unique();
            $table->json('profile');
            $table->json('technical');
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('updated_by_user_id')->constrained('crm_users');
            $table->timestamps();
        });

        Schema::create('crm_lead_detail_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('lead_source_id')->index();
            $table->unsignedInteger('version');
            $table->foreignId('changed_by_user_id')->constrained('crm_users');
            $table->json('changes');
            $table->timestamp('created_at');
            $table->unique(['lead_source_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_lead_detail_revisions');
        Schema::dropIfExists('crm_lead_details');
    }
};
