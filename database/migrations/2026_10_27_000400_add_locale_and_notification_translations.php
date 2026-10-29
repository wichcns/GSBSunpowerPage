<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_users', fn (Blueprint $table) => $table->string('locale', 5)->default('th')->after('role_id'));
        Schema::table('crm_notifications', fn (Blueprint $table) => $table->json('translations')->nullable()->after('body'));
    }

    public function down(): void
    {
        Schema::table('crm_notifications', fn (Blueprint $table) => $table->dropColumn('translations'));
        Schema::table('crm_users', fn (Blueprint $table) => $table->dropColumn('locale'));
    }
};
