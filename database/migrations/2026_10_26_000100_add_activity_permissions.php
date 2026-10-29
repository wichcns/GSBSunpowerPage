<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleIds = DB::table('crm_roles')->whereIn('slug', ['manager', 'staff'])->pluck('id');
        foreach ($roleIds as $roleId) {
            DB::table('crm_role_permissions')->insertOrIgnore([
                'role_id' => $roleId,
                'permission' => 'activities.view',
            ]);
        }
    }

    public function down(): void
    {
        $roleIds = DB::table('crm_roles')->whereIn('slug', ['manager', 'staff'])->pluck('id');
        DB::table('crm_role_permissions')->whereIn('role_id', $roleIds)
            ->where('permission', 'activities.view')->delete();
    }
};
