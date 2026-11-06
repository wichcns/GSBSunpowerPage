<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_archived_opportunities', function (Blueprint $table) {
            $table->string('source_id')->primary();
            $table->foreignId('archived_by_user_id')->constrained('crm_users');
            $table->timestamp('archived_at');
        });

        Schema::table('crm_attachments', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('crm_users');
        });
    }

    public function down(): void
    {
        Schema::table('crm_attachments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by_user_id');
            $table->dropColumn('deleted_at');
        });
        Schema::dropIfExists('crm_archived_opportunities');
    }
};
