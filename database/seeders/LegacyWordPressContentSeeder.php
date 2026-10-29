<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyWordPressContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/legacy-wordpress-content.json')), true, 512, JSON_THROW_ON_ERROR);
        $now = now();

        DB::table('site_settings')->updateOrInsert(['key' => 'legacy_wordpress_options'], [
            'value' => json_encode($data['options'], JSON_UNESCAPED_UNICODE), 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach (['ppa', 'epc', 'ev'] as $type) {
            foreach ($data[$type] as $order => $project) {
                $imagePath = $this->toLocalPath($project['image_url'] ?? null);
                $gallery = array_values(array_filter(array_map(fn ($url) => $this->toLocalPath($url), $project['gallery_urls'] ?? [])));
                DB::table('projects')->updateOrInsert(['legacy_key' => $project['internal_title']], [
                    'type' => $type,
                    'title_en' => $project['title_th'] ?? null,
                    'title_th' => $project['title_th'] ?? null,
                    'title_zh' => $project['title_zh'] ?? null,
                    'scale_th' => $project['scale_th'] ?? null, 'scale_zh' => $project['scale_zh'] ?? null,
                    'configuration_th' => $project['config_th'] ?? null, 'configuration_zh' => $project['config_zh'] ?? null,
                    'location_th' => $project['location_th'] ?? null, 'location_zh' => $project['location_zh'] ?? null,
                    'model_th' => $project['model_th'] ?? null, 'model_zh' => $project['model_zh'] ?? null,
                    'overview_th' => $project['overview_th'] ?? null, 'overview_zh' => $project['overview_zh'] ?? null,
                    'status_th' => $project['status_th'] ?? null, 'status_zh' => $project['status_zh'] ?? null,
                    'image_path' => $imagePath, 'gallery_paths' => json_encode($gallery), 'sort_order' => $order + 1,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        foreach ($data['career'] as $career) {
            DB::table('careers')->updateOrInsert(['legacy_key' => $career['internal_title']], [
                'title_th' => $career['title_th'], 'title_zh' => $career['title_zh'],
                'location_th' => $career['location_th'] ?? null, 'location_zh' => $career['location_zh'] ?? null,
                'salary_th' => $career['salary_th'] ?? null, 'salary_zh' => $career['salary_zh'] ?? null,
                'description_th' => $career['description_th'] ?? null, 'description_zh' => $career['description_zh'] ?? null,
                'email' => $career['email'] ?? null, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function toLocalPath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $path = preg_replace('/.*assets\\/images\\//', '', $url);

        return $path ? 'legacy/images/'.str_replace('\\\\', '/', $path) : null;
    }
}
