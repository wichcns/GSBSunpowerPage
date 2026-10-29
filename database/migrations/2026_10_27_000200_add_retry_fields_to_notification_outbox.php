<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_notification_outbox', function (Blueprint $table) {
            $table->uuid('retry_key')->nullable()->after('attempts');
            $table->timestamp('next_attempt_at')->nullable()->after('retry_key');
            $table->timestamp('processing_at')->nullable()->after('next_attempt_at');
            $table->index(['status', 'next_attempt_at'], 'crm_outbox_retry_due_index');
        });

        DB::table('crm_notification_outbox')
            ->whereNull('retry_key')
            ->orderBy('id')
            ->eachById(function (object $item): void {
                DB::table('crm_notification_outbox')->where('id', $item->id)->update([
                    'retry_key' => (string) Str::uuid(),
                ]);
            });

        Schema::table('crm_notification_outbox', function (Blueprint $table) {
            $table->unique('retry_key', 'crm_outbox_retry_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('crm_notification_outbox', function (Blueprint $table) {
            $table->dropUnique('crm_outbox_retry_key_unique');
            $table->dropIndex('crm_outbox_retry_due_index');
            $table->dropColumn(['retry_key', 'next_attempt_at', 'processing_at']);
        });
    }
};
