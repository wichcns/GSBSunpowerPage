<?php

namespace Database\Seeders;

use App\Models\Solution;
use Illuminate\Database\Seeder;

class SolutionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['ev-charging', 'EV Charging', 'สถานีชาร์จ EV', '电动车充电', 'Charging infrastructure designed around your drivers, visitors and daily operation.', 'โครงสร้างพื้นฐานสถานีชาร์จ EV ที่ออกแบบให้เหมาะกับผู้ใช้งาน พื้นที่ และการดำเนินงานของคุณ', '围绕驾驶者、访客和日常运营打造的充电基础设施', 'legacy/images/charge_photo/charge_site_04_khun_ton.jpeg', 'ev-charging.html'],
            ['solar-ppa', 'Solar PPA', 'Solar PPA', '太阳能 PPA', 'Access solar energy without an upfront asset investment.', 'ใช้พลังงานจากโซลาร์เซลล์โดยไม่ต้องลงทุนในสินทรัพย์ล่วงหน้า', '无需前期资产投资即可使用太阳能', 'image/SolarPPA/ppa_case_08_rayong_market.jpg', 'solar-ppa.html'],
            ['engineering-epc', 'Engineering & EPC', 'วิศวกรรมและ EPC', '工程与 EPC', 'One accountable team from engineering through construction and handover.', 'ทีมเดียวดูแลตั้งแต่งานวิศวกรรม ก่อสร้าง จนถึงการส่งมอบ', '从工程设计、施工到交付，由一个团队全程负责', 'image/SolarPPA/ppa_case_13_prachuap_wood.png', 'epc.html'],
        ] as $index => [$slug, $en, $th, $zh, $summaryEn, $summaryTh, $summaryZh, $image, $url]) {
            Solution::query()->updateOrCreate(['slug' => $slug], [
                'title_en' => $en,
                'title_th' => $th,
                'title_zh' => $zh,
                'summary_en' => $summaryEn,
                'summary_th' => $summaryTh,
                'summary_zh' => $summaryZh,
                'image_path' => $image,
                'destination_url' => $url,
                'sort_order' => $index + 1,
                'is_published' => true,
            ]);
        }
    }
}
