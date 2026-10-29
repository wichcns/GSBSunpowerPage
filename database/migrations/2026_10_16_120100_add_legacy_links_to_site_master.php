<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_sites', function (Blueprint $table) {
            $table->string('legacy_lead_id')->nullable()->unique();
            $table->string('legacy_owner_label')->nullable();
        });

        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->string('legacy_lead_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('crm_contacts', fn (Blueprint $table) => $table->dropColumn('legacy_lead_id'));
        Schema::table('crm_sites', fn (Blueprint $table) => $table->dropColumn(['legacy_lead_id', 'legacy_owner_label']));
    }
};
