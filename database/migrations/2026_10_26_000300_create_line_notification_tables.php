<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_line_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_id', 64)->unique();
            $table->string('display_name')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('crm_notification_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('channel', 30);
            $table->string('recipient', 100)->nullable();
            $table->string('notification_type', 60);
            $table->json('payload');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_notification_outbox');
        Schema::dropIfExists('crm_line_groups');
    }
};
