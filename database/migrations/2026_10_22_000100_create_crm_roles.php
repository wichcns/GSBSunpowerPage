<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });
        Schema::create('crm_role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('crm_roles')->cascadeOnDelete();
            $table->string('permission');
            $table->primary(['role_id', 'permission']);
        });
        Schema::table('crm_users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('crm_roles')->nullOnDelete();
        });

        $roles = [
            'super_admin' => ['Super Admin', []],
            'admin' => ['Admin', ['dashboard.view', 'work.view', 'sites.view', 'sites.write', 'ownership.manage',
                'pipeline.view', 'pipeline.stage', 'pipeline.details', 'pipeline.business', 'pipeline.log',
                'contracts.view', 'engineering.view', 'engineering.manage', 'engineering.log',
                'activities.view', 'reports.view']],
            'manager' => ['Manager', ['dashboard.view', 'work.view', 'sites.view', 'sites.write', 'ownership.manage',
                'pipeline.view', 'pipeline.stage', 'pipeline.log', 'contracts.view',
                'engineering.view', 'engineering.manage', 'engineering.log']],
            'staff' => ['Staff', ['dashboard.view', 'work.view', 'pipeline.view', 'pipeline.log',
                'engineering.view', 'engineering.log']],
        ];
        foreach ($roles as $slug => [$name, $permissions]) {
            $id = DB::table('crm_roles')->insertGetId([
                'slug' => $slug, 'name' => $name, 'is_system' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($permissions as $permission) {
                DB::table('crm_role_permissions')->insert(['role_id' => $id, 'permission' => $permission]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('crm_users', fn (Blueprint $table) => $table->dropConstrainedForeignId('role_id'));
        Schema::dropIfExists('crm_role_permissions');
        Schema::dropIfExists('crm_roles');
    }
};
