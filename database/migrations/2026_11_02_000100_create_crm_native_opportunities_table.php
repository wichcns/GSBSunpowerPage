<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_native_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained('crm_sites')->cascadeOnDelete();
            $table->string('source_id')->unique();
            $table->json('payload');
            $table->foreignId('created_by_user_id')->constrained('crm_users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_native_opportunities');
    }
};
