<?php

namespace Tests\Feature\CrmMigrated;

use App\Crm\Actions\ChangeSalesStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CrmLocaleTest extends CrmTestCase
{
    use RefreshDatabase;

    public function test_guest_pages_use_the_saved_crm_language(): void
    {
        $this->withSession(['locale' => 'zh'])
            ->get('/crm/forgot-password')
            ->assertOk()
            ->assertSee('请输入您的电子邮箱');

        $this->withSession(['locale' => 'th'])
            ->get('/crm/forgot-password')
            ->assertOk()
            ->assertSee('กรอกอีเมลของคุณ');
    }

    public function test_sales_stage_error_uses_the_selected_language(): void
    {
        app()->setLocale('zh');

        try {
            app(ChangeSalesStage::class)->execute('unused', 'INVALID', 'unused', 1);
            $this->fail('An invalid stage must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame('销售阶段无效。', $exception->getMessage());
        }
    }
}
