<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_an_administrator_account(): void
    {
        $this->seed(AdminUserSeeder::class);
        $administrator = User::query()->where('email', 'marketing.gsbsunpower@gmail.com')->firstOrFail();

        $this->assertSame('admin', $administrator->name);
        $this->assertTrue($administrator->is_admin);
        $this->assertNotEmpty($administrator->password);
        $this->actingAs($administrator)->get(route('admin.dashboard'))->assertOk();
    }
}
