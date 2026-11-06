<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['manager', 'staff'] as $slug) {
            $roleId = DB::table('crm_roles')->where('slug', $slug)->value('id');
            if ($roleId === null) {
                continue;
            }
            foreach (['pipeline.stage', 'pipeline.details', 'pipeline.business'] as $permission) {
                DB::table('crm_role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission' => $permission,
                ]);
            }
        }
    }

    public function down(): void
    {
        $managerId = DB::table('crm_roles')->where('slug', 'manager')->value('id');
        $staffId = DB::table('crm_roles')->where('slug', 'staff')->value('id');
        if ($managerId !== null) {
            DB::table('crm_role_permissions')->where('role_id', $managerId)
                ->whereIn('permission', ['pipeline.details', 'pipeline.business'])->delete();
        }
        if ($staffId !== null) {
            DB::table('crm_role_permissions')->where('role_id', $staffId)
                ->whereIn('permission', ['pipeline.stage', 'pipeline.details', 'pipeline.business'])->delete();
        }
    }
};
