<?php

namespace Tests\Unit;

use App\Crm\Support\SalesStageGroups;
use Tests\TestCase;

class SalesStageGroupsTest extends TestCase
{
    public function test_current_and_imported_stages_share_eight_clear_display_groups(): void
    {
        $expected = [
            'NEW' => 'NEW', 'NEGOTIATING' => 'NEGOTIATING',
            'REVIEW' => 'REVIEW', 'INTERNAL_REVIEW' => 'REVIEW', 'INTERNAL_APPROVED' => 'REVIEW',
            'MOU' => 'MOU', 'MOU_SIGNING' => 'MOU', 'MOU_SIGNED' => 'MOU',
            'CONTRACT' => 'CONTRACT', 'CONTRACT_SIGNING' => 'CONTRACT',
            'SIGNED' => 'SIGNED', 'CONTRACT_SIGNED' => 'SIGNED',
            'ON_HOLD' => 'ON_HOLD', 'CANCELLED' => 'CANCELLED',
        ];

        $this->assertSame(8, count(SalesStageGroups::codes()));
        foreach ($expected as $stored => $display) {
            $this->assertSame($display, SalesStageGroups::forStage($stored));
        }
        $this->assertSame('OTHER', SalesStageGroups::forStage('UNRECOGNIZED'));
    }

    public function test_group_labels_exist_in_all_three_locales(): void
    {
        foreach (['th', 'en', 'zh'] as $locale) {
            app()->setLocale($locale);
            foreach (SalesStageGroups::codes() as $code) {
                $this->assertNotSame('sales_stage_groups.'.$code, __('sales_stage_groups.'.$code));
            }
        }
    }
}
