<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'marketing.gsbsunpower@gmail.com'],
            [
                'name' => 'admin',
                'email_verified_at' => now(),
                'password' => '$2y$12$eEvf7AA.C6asrN4ekkqTE.mKZ1P1C37v/LPNYsOeRcnzf.t.GbuFi',
                'is_admin' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
