<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('crm_notification_settings')->insertOrIgnore([
            'event_type' => 'construction_progress_updated',
            'is_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('crm_notification_settings')->where('event_type', 'construction_progress_updated')->delete();
    }
};
