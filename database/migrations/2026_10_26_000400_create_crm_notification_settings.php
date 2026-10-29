<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_notification_settings', function (Blueprint $table) {
            $table->string('event_type', 60)->primary();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });

        foreach (['site_owner_assigned', 'sales_stage_changed', 'engineering_stage_changed',
            'construction_started', 'service_opened', 'task_due_soon', 'task_overdue'] as $event) {
            DB::table('crm_notification_settings')->insert([
                'event_type' => $event, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_notification_settings');
    }
};
