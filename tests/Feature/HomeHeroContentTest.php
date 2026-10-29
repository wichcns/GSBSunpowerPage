<?php

namespace Tests\Feature;

use App\HomeHeroContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeHeroContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_the_managed_hero_copy_when_it_exists(): void
    {
        $content = HomeHeroContent::defaults();
        $content['en']['heading'] = 'Managed solar copy';

        DB::table('site_settings')->insert([
            'key' => 'home_hero',
            'value' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertOk()->assertSee('Managed solar copy');
    }
}
