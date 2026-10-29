<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_visit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('lead_source_id');
            $table->string('visit_type', 30);
            $table->dateTime('occurred_at');
            $table->string('internal_participants', 500);
            $table->string('customer_participants', 500)->nullable();
            $table->string('location', 500)->nullable();
            $table->text('summary');
            $table->text('site_findings')->nullable();
            $table->text('decisions')->nullable();
            $table->text('risks')->nullable();
            $table->text('next_steps');
            $table->dateTime('next_action_at')->nullable();
            $table->foreignId('created_by_user_id')->constrained('crm_users');
            $table->timestamps();
            $table->index(['lead_source_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_visit_logs');
    }
};
