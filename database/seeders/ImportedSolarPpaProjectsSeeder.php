<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportedSolarPpaProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'legacy_key' => 'ppa-fin-international-thailand',
                'sort_order' => 16,
                'title_th' => 'บริษัท เอฟไอเอ็น อินเตอร์เนชั่นแนล (ไทยแลนด์) จำกัด',
                'title_en' => 'F-IN International Thailand Co., Ltd.',
                'title_zh' => 'F-IN 国际（泰国）有限公司',
                'scale' => '0.7 MW',
                'location_th' => 'นิคมอุตสาหกรรมอมตะซิตี้ ระยอง อำเภอปลวกแดง จังหวัดระยอง',
                'location_en' => 'Amata City Rayong Industrial Estate, Pluak Daeng, Rayong',
                'location_zh' => '罗勇府普拉克登县阿玛塔城工业园',
                'overview_th' => 'บริษัทผู้นำทางด้านเทคนิคและมีความเชี่ยวชาญทางการผลิตสารเคมียางรถยนต์ รวมถึงผลิตถุงพลาสติก PE/EVA สำหรับอุตสาหกรรมยางรถยนต์',
                'overview_en' => 'A technical leader in tire-chemical manufacturing and PE/EVA plastic bags for the tire industry.',
                'overview_zh' => '专注于轮胎化学品生产，并生产用于轮胎行业的 PE/EVA 塑料袋。',
                'images' => $this->images('fin-international', 6),
            ],
            [
                'legacy_key' => 'ppa-fin-specialty-chemicals-thailand',
                'sort_order' => 17,
                'title_th' => 'บริษัท เอฟไอเอ็น อินเตอร์แนชั่นแนล สเปเชียลตี้ เคมีคอลส์ (ไทยแลนด์) จำกัด',
                'title_en' => 'F-IN International Specialty Chemicals Thailand Co., Ltd.',
                'title_zh' => 'F-IN 国际特种化学品（泰国）有限公司',
                'scale' => '1.8 MW',
                'location_th' => 'นิคมอุตสาหกรรมอมตะซิตี้ ระยอง อำเภอปลวกแดง จังหวัดระยอง',
                'location_en' => 'Amata City Rayong Industrial Estate, Pluak Daeng, Rayong',
                'location_zh' => '罗勇府普拉克登县阿玛塔城工业园',
                'overview_th' => 'ประกอบธุรกิจให้เช่าและดำเนินการเกี่ยวกับอสังหาริมทรัพย์ของตนเองหรือเช่าจากผู้อื่น ซึ่งไม่ใช่เพื่อการพักอาศัย',
                'overview_en' => 'Provides leasing and operational services for owned or leased non-residential real estate.',
                'overview_zh' => '从事自有或租赁非住宅房地产的出租与运营业务。',
                'images' => $this->images('fin-specialty', 6),
            ],
            [
                'legacy_key' => 'ppa-ming-xin-thailand',
                'sort_order' => 18,
                'title_th' => 'บริษัท หมิงซิน (ไทยแลนด์) จำกัด',
                'title_en' => 'Ming Xin Thailand Co., Ltd.',
                'title_zh' => '明新（泰国）有限公司',
                'scale' => '1.7 MW',
                'location_th' => 'ตำบลหนองบอนแดง อำเภอบ้านบึง จังหวัดชลบุรี',
                'location_en' => 'Nong Bon Daeng, Ban Bueng, Chonburi',
                'location_zh' => '春武里府班邦县农邦登镇',
                'overview_th' => 'ผู้ผลิตผลิตภัณฑ์แปรรูปอื่น ๆ จากกระดาษและกระดาษแข็ง ซึ่งมิได้จัดประเภทไว้ในที่อื่น',
                'overview_en' => 'Manufacturer of other converted paper and paperboard products not elsewhere classified.',
                'overview_zh' => '从事其他未分类纸制品及纸板制品的生产。',
                'images' => $this->images('ming-xin', 6),
            ],
            [
                'legacy_key' => 'ppa-lijiaceng-thailand',
                'sort_order' => 19,
                'title_th' => 'บริษัท ลี่เจียเฉง ประเทศไทย จำกัด',
                'title_en' => 'Lijiaceng Thailand Co., Ltd.',
                'title_zh' => '丽嘉成泰国有限公司',
                'scale' => '1 MW',
                'location_th' => 'ตำบลท่าเสา อำเภอกระทุ่มแบน จังหวัดสมุทรสาคร',
                'location_en' => 'Tha Sao, Krathum Baen, Samut Sakhon',
                'location_zh' => '沙没沙空府甲吞本县塔骚镇',
                'overview_th' => 'ประกอบกิจการนำเข้า ส่งออก ผลิต ประกอบ และจำหน่ายหลอดไฟ LED ทุกชนิด รวมถึงอุปกรณ์ตัดไฟอัตโนมัติ',
                'overview_en' => 'Imports, exports, manufactures, assembles and sells LED lighting and automatic circuit-breaker equipment.',
                'overview_zh' => '从事各类 LED 灯具及自动断路器设备的进口、出口、生产、组装与销售。',
                'images' => $this->images('lijiaceng', 6),
            ],
            [
                'legacy_key' => 'ppa-himile-thailand',
                'sort_order' => 20,
                'title_th' => 'บริษัท ไฮไมล์ (ไทยแลนด์) จำกัด',
                'title_en' => 'Himile Thailand Co., Ltd.',
                'title_zh' => '海迈（泰国）有限公司',
                'scale' => '4.815 MW',
                'location_th' => 'นิคมอุตสาหกรรมอมตะซิตี้ ระยอง อำเภอปลวกแดง จังหวัดระยอง',
                'location_en' => 'Amata City Rayong Industrial Estate, Pluak Daeng, Rayong',
                'location_zh' => '罗勇府普拉克登县阿玛塔城工业园',
                'overview_th' => 'ประกอบกิจการผลิตและซ่อมแซมแม่พิมพ์ล้อรถทุกชนิด โดยมีลูกค้ายางรถยนต์แบรนด์ชั้นนำ',
                'overview_en' => 'Manufactures and repairs wheel moulds for leading tire-brand customers.',
                'overview_zh' => '从事各类车轮模具的生产与维修，服务于知名轮胎品牌客户。',
                'images' => $this->images('himile', 6),
            ],
            [
                'legacy_key' => 'ppa-palawood-pallet',
                'sort_order' => 21,
                'title_th' => 'ห้างหุ้นส่วนจำกัด พาราวู๊ดพาเลท',
                'title_en' => 'Palawood Pallet Limited Partnership',
                'title_zh' => '帕拉伍德托盘有限合伙企业',
                'scale' => '119.4 kWh',
                'location_th' => 'ตำบลแม่รำพึง อำเภอบางสะพาน จังหวัดประจวบคีรีขันธ์',
                'location_en' => 'Mae Ramphueng, Bang Saphan, Prachuap Khiri Khan',
                'location_zh' => '巴蜀府邦沙潘县湄兰蓬镇',
                'overview_th' => 'ประกอบกิจการผลิตและซ่อมแซมแม่พิมพ์ล้อรถทุกชนิด โดยมีลูกค้ายางรถยนต์แบรนด์ชั้นนำ',
                'overview_en' => 'Manufactures and repairs wheel moulds for leading tire-brand customers.',
                'overview_zh' => '从事各类车轮模具的生产与维修，服务于知名轮胎品牌客户。',
                'images' => $this->images('palawood', 1, 'png'),
            ],
        ];

        foreach ($projects as $project) {
            $images = $project['images'];
            $scale = $project['scale'];
            unset($project['images'], $project['scale']);

            DB::table('projects')->updateOrInsert(
                ['legacy_key' => $project['legacy_key']],
                [
                    ...$project,
                    'type' => 'ppa',
                    'scale_th' => $scale,
                    'scale_en' => $scale,
                    'scale_zh' => $scale,
                    'configuration_th' => 'Solar PPA',
                    'configuration_en' => 'Solar PPA',
                    'configuration_zh' => '太阳能 PPA',
                    'model_th' => 'Solar PPA',
                    'model_en' => 'Solar PPA',
                    'model_zh' => '太阳能 PPA',
                    'status_th' => 'ติดตั้งและดำเนินการแล้ว',
                    'status_zh' => '已安装并投入运营',
                    'image_path' => $images[0],
                    'gallery_paths' => json_encode(array_slice($images, 1)),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    private function images(string $prefix, int $count, string $extension = 'jpg'): array
    {
        return collect(range(1, $count))
            ->map(fn (int $number) => 'uploads/projects/solar-portfolio-2026/'.$prefix.'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'.'.$extension)
            ->all();
    }
}
