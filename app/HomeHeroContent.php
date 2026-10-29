<?php

namespace App;

class HomeHeroContent
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function defaults(): array
    {
        return [
            'en' => ['label' => 'GSB SUNPOWER GROUP', 'heading' => 'Solar and EV', 'emphasis' => 'One team One clear path', 'lead' => 'Clean-energy infrastructure, planned around your business — from solar to EV charging', 'prompt' => 'Choose a service to start', 'consult' => 'Get a consultation'],
            'th' => ['label' => 'GSB SUNPOWER GROUP', 'heading' => 'พลังงานแสงอาทิตย์และ EV', 'emphasis' => 'ทีมเดียว ดูแลครบ', 'lead' => 'โครงสร้างพื้นฐานด้านพลังงานสะอาดที่ออกแบบให้เหมาะกับธุรกิจของคุณ ตั้งแต่ระบบโซลาร์เซลล์ไปจนถึงสถานีชาร์จ EV', 'prompt' => 'เลือกบริการเพื่อเริ่มต้น', 'consult' => 'ขอรับคำปรึกษา'],
            'zh' => ['label' => 'GSB SUNPOWER GROUP', 'heading' => '太阳能与电动汽车充电', 'emphasis' => '一支团队，一条清晰路径', 'lead' => '围绕您的业务规划清洁能源基础设施，从太阳能到电动汽车充电', 'prompt' => '选择一项服务开始', 'consult' => '获取咨询'],
        ];
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, array<string, string>>
     */
    public static function merge(array $content): array
    {
        $defaults = self::defaults();

        foreach ($defaults as $locale => $values) {
            $localeContent = is_array($content[$locale] ?? null) ? $content[$locale] : [];
            $defaults[$locale] = array_merge($values, array_filter($localeContent, 'is_string'));
        }

        return $defaults;
    }
}
