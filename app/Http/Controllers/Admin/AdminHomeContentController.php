<?php

namespace App\Http\Controllers\Admin;

use App\HomeHeroContent;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHomeContentController extends Controller
{
    public function editHero(): View
    {
        $content = DB::table('site_settings')->where('key', 'home_hero')->value('value');
        $homeHero = HomeHeroContent::merge(is_string($content) ? json_decode($content, true) ?: [] : []);

        return view('admin.website-content.home-hero', compact('homeHero'));
    }

    public function updateHero(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'array'],
            'content.en' => ['required', 'array'],
            'content.th' => ['required', 'array'],
            'content.zh' => ['required', 'array'],
            'content.*.label' => ['required', 'string', 'max:120'],
            'content.*.heading' => ['required', 'string', 'max:160'],
            'content.*.emphasis' => ['required', 'string', 'max:160'],
            'content.*.lead' => ['required', 'string', 'max:500'],
            'content.*.prompt' => ['required', 'string', 'max:160'],
            'content.*.consult' => ['required', 'string', 'max:100'],
        ]);

        DB::table('site_settings')->updateOrInsert(['key' => 'home_hero'], ['value' => json_encode(HomeHeroContent::merge($validated['content']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'updated_at' => now(), 'created_at' => now()]);

        return redirect()->route('admin.home-content.hero.edit')->with('success', 'บันทึกข้อความ Banner หน้าแรกเรียบร้อยแล้ว');
    }
}
