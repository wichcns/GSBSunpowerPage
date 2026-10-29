<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('lead_source_id');
            $table->foreignId('visit_log_id')->nullable()->constrained('crm_visit_logs')->nullOnDelete();
            $table->string('category', 30);
            $table->string('original_name', 240);
            $table->string('storage_path')->unique();
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->string('note', 1000)->nullable();
            $table->foreignId('uploaded_by_user_id')->constrained('crm_users');
            $table->timestamps();
            $table->index(['lead_source_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_attachments');
    }
};
