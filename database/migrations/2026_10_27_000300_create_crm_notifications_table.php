<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('crm_users')->cascadeOnDelete();
            $table->string('event_key');
            $table->string('type', 60);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('action_url', 1000)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'event_key'], 'crm_notifications_user_event_unique');
            $table->index(['user_id', 'read_at'], 'crm_notifications_unread_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_notifications');
    }
};
