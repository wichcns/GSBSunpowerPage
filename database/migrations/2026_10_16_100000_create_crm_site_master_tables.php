<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_code')->nullable()->unique();
            $table->string('name');
            $table->string('area')->nullable();
            $table->string('location_type')->nullable();
            $table->text('map_url')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['area', 'location_type']);
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('crm_sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('line_name')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['site_id', 'is_primary']);
        });

        Schema::create('crm_site_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('crm_sites')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->string('action');
            $table->json('changed_fields')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_site_audits');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_sites');
    }
};
