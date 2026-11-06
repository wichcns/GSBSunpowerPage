<?php

namespace Tests\Feature\CrmMigrated\Admin;

use App\Crm\Models\Role;
use App\Crm\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\CrmMigrated\CrmTestCase as TestCase;

class ManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_manuals_require_login_and_only_allow_the_two_languages(): void
    {
        $this->get('/crm/admin/manuals')->assertRedirect('/crm/login');
        $this->get('/crm/admin/manuals/th/file')->assertRedirect('/crm/login');

        $this->actingAs(User::factory()->create())
            ->get('/crm/admin/manuals/de/file')->assertNotFound();
    }

    public function test_each_role_can_choose_a_manual_and_open_its_private_pdf(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('crm/manuals/GSB_EV_CRM_Thai.pdf', '%PDF-1.4 Thai manual');
        Storage::disk('local')->put('crm/manuals/GSB_EV_CRM_Chinese.pdf', '%PDF-1.4 Chinese manual');

        foreach (['super_admin', 'admin', 'manager', 'staff'] as $slug) {
            $user = User::factory()->create(['role_id' => Role::query()->where('slug', $slug)->value('id')]);

            $this->actingAs($user)->get('/crm/admin/manuals?language=th')->assertOk()
                ->assertSee('TH')->assertSee('CH')
                ->assertSee('crm/admin/manuals/th/file');
            $this->get('/crm/admin/manuals?language=zh')->assertOk()
                ->assertSee('crm/admin/manuals/zh/file');
            $this->get('/crm/admin/manuals/th/file')->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->get('/crm/admin/manuals/zh/file?download=1')->assertOk()
                ->assertHeader('Content-Disposition', 'attachment; filename="GSB_EV_CRM_Chinese.pdf"');
        }
    }

    public function test_missing_private_manual_returns_not_found(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create())
            ->get('/crm/admin/manuals/th/file')->assertNotFound();
    }
}
