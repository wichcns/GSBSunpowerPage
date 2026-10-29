<?php

namespace App\Http\Controllers\PublicSite;

use App\HomeHeroContent;
use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomePageController extends Controller
{
    public function __invoke(): View
    {
        $content = DB::table('site_settings')->where('key', 'home_hero')->value('value');
        $homeHero = HomeHeroContent::merge(is_string($content) ? json_decode($content, true) ?: [] : []);

        $testimonials = Testimonial::query()->where('is_published', true)->orderBy('sort_order')->get();
        $clientLogos = ClientLogo::query()
            ->where('is_published', true)
            ->orderBy('rail')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('rail');

        $leftClientLogos = $clientLogos->get('left', collect());
        $rightClientLogos = $clientLogos->get('right', collect());

        return view('pages.home-experience', compact(
            'homeHero',
            'testimonials',
            'leftClientLogos',
            'rightClientLogos',
        ));
    }
}
