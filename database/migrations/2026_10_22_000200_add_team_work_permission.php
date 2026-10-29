<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['admin', 'manager'] as $slug) {
            $roleId = DB::table('crm_roles')->where('slug', $slug)->value('id');
            if ($roleId !== null) {
                DB::table('crm_role_permissions')->insertOrIgnore(['role_id' => $roleId, 'permission' => 'team.view']);
            }
        }
        $managerId = DB::table('crm_roles')->where('slug', 'manager')->value('id');
        if ($managerId !== null) {
            DB::table('crm_role_permissions')->where('role_id', $managerId)->where('permission', 'work.view')->delete();
        }
    }

    public function down(): void
    {
        $managerId = DB::table('crm_roles')->where('slug', 'manager')->value('id');
        if ($managerId !== null) {
            DB::table('crm_role_permissions')->insertOrIgnore(['role_id' => $managerId, 'permission' => 'work.view']);
        }
        DB::table('crm_role_permissions')->where('permission', 'team.view')->delete();
    }
};
