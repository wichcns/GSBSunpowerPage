<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_engineering_construction_records', function (Blueprint $table) {
            $table->foreignId('updated_by_user_id')->nullable();
            $table->foreign('updated_by_user_id', 'crm_construction_actor_fk')->references('id')->on('crm_users');
        });
    }

    public function down(): void
    {
        Schema::table('crm_engineering_construction_records', function (Blueprint $table) {
            $table->dropForeign('crm_construction_actor_fk');
            $table->dropColumn('updated_by_user_id');
        });
    }
};
