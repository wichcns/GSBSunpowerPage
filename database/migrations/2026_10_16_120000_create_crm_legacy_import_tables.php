<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_legacy_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('source_sha256', 64)->unique();
            $table->string('source_name');
            $table->string('exported_at');
            $table->json('source_counts');
            $table->json('imported_counts');
            $table->json('validation_report');
            $table->string('status');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_legacy_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('crm_legacy_import_batches')->cascadeOnDelete();
            $table->string('source_table');
            $table->string('source_id');
            $table->longText('payload');
            $table->string('payload_sha256', 64);
            $table->timestamps();
            $table->unique(['batch_id', 'source_table', 'source_id'], 'crm_legacy_records_source_unique');
            $table->index(['source_table', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_legacy_records');
        Schema::dropIfExists('crm_legacy_import_batches');
    }
};
