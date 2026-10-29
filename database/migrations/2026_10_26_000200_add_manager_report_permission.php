<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('crm_roles')->where('slug', 'manager')->value('id');
        if ($roleId) {
            DB::table('crm_role_permissions')->insertOrIgnore(['role_id' => $roleId, 'permission' => 'reports.view']);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('crm_roles')->where('slug', 'manager')->value('id');
        if ($roleId) {
            DB::table('crm_role_permissions')->where('role_id', $roleId)->where('permission', 'reports.view')->delete();
        }
    }
};
