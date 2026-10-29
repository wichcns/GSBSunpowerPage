<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminSectionController extends Controller
{
    public function websiteContent(): View
    {
        $publishedProjects = DB::table('projects')->where('is_published', true)->count();
        $draftProjects = DB::table('projects')->where('is_published', false)->count();

        return view('admin.website-content.index', [
            'summary' => [
                ['label' => 'โครงการที่เผยแพร่', 'value' => $publishedProjects, 'icon' => 'fas fa-globe-asia', 'tone' => 'teal'],
                ['label' => 'โครงการฉบับร่าง', 'value' => $draftProjects, 'icon' => 'fas fa-file-alt', 'tone' => 'amber'],
                ['label' => 'โซลูชันบนเว็บไซต์', 'value' => DB::table('solutions')->where('is_published', true)->count(), 'icon' => 'fas fa-layer-group', 'tone' => 'blue'],
                ['label' => 'ข้อความใหม่', 'value' => DB::table('contact_enquiries')->where('status', 'new')->count(), 'icon' => 'fas fa-inbox', 'tone' => 'purple'],
            ],
            'contentGroups' => [
                [
                    'eyebrow' => 'ABOUT US',
                    'title' => 'Organization Chart',
                    'description' => 'เปลี่ยนเฉพาะรูป Chart ที่แสดงในหน้า About us',
                    'icon' => 'fas fa-sitemap',
                    'tone' => 'blue',
                    'items' => [
                        ['label' => 'เปลี่ยนรูป Chart', 'count' => 'A', 'route' => route('admin.about-content.chart.edit')],
                    ],
                ],
                [
                    'eyebrow' => 'PARTNER FEEDBACK',
                    'title' => 'รีวิวลูกค้า',
                    'description' => 'แก้ไขข้อความ คะแนนดาว ลำดับ และสถานะเผยแพร่ของรีวิวบนหน้าแรก',
                    'icon' => 'fas fa-comment-dots',
                    'tone' => 'purple',
                    'items' => [
                        ['label' => 'จัดการรีวิว', 'count' => DB::table('testimonials')->count(), 'route' => route('admin.testimonials.index')],
                    ],
                ],
                [
                    'eyebrow' => 'CLIENT LOGOS',
                    'title' => 'โลโก้ลูกค้า',
                    'description' => 'เพิ่ม ลบ และกำหนดแถวเลื่อนซ้ายหรือขวาของโลโก้บนหน้าแรก',
                    'icon' => 'fas fa-images',
                    'tone' => 'teal',
                    'items' => [
                        ['label' => 'จัดการโลโก้', 'count' => DB::table('client_logos')->count(), 'route' => route('admin.client-logos.index')],
                    ],
                ],
                [
                    'eyebrow' => 'HOME PAGE',
                    'title' => 'Banner หน้าแรก',
                    'description' => 'แก้ไขข้อความนำ หัวข้อ คำอธิบาย และปุ่มปรึกษาบนหน้าแรกได้ 3 ภาษา',
                    'icon' => 'fas fa-image',
                    'tone' => 'amber',
                    'items' => [
                        ['label' => 'แก้ไขข้อความ Banner', 'count' => 'A', 'route' => route('admin.home-content.hero.edit')],
                    ],
                ],
                [
                    'eyebrow' => 'BLOG / INSIGHTS',
                    'title' => 'บทความและคลิป',
                    'description' => 'เพิ่มบทความ ภาพปก คลิป และกำหนดสถานะเผยแพร่บนหน้า Blog ได้ 3 ภาษา',
                    'icon' => 'fas fa-newspaper',
                    'tone' => 'purple',
                    'items' => [
                        ['label' => 'จัดการ Blog', 'count' => DB::table('blog_posts')->count(), 'route' => route('admin.blog.index')],
                    ],
                ],
                [
                    'eyebrow' => 'PROJECTS',
                    'title' => 'ผลงานและโครงการ',
                    'description' => 'เพิ่ม แก้ไขรูปภาพ จัดลำดับ และเปิดหรือซ่อนผลงานบนหน้าบ้าน',
                    'icon' => 'fas fa-briefcase',
                    'tone' => 'teal',
                    'items' => [
                        ['label' => 'Solar PPA', 'count' => DB::table('projects')->where('type', 'ppa')->count(), 'route' => route('admin.projects', 'ppa')],
                        ['label' => 'EPC', 'count' => DB::table('projects')->where('type', 'epc')->count(), 'route' => route('admin.projects', 'epc')],
                        ['label' => 'EV Charging', 'count' => DB::table('projects')->where('type', 'ev')->count(), 'route' => route('admin.projects', 'ev')],
                    ],
                ],
                [
                    'eyebrow' => 'SOLUTIONS',
                    'title' => 'บริการและโซลูชัน',
                    'description' => 'จัดการชื่อ รายละเอียด รูปภาพ และสถานะเผยแพร่ของบริการ',
                    'icon' => 'fas fa-solar-panel',
                    'tone' => 'blue',
                    'items' => [
                        ['label' => 'จัดการโซลูชัน', 'count' => DB::table('solutions')->count(), 'route' => route('admin.solutions.index')],
                    ],
                ],
                [
                    'eyebrow' => 'INBOX',
                    'title' => 'ข้อความจากเว็บไซต์',
                    'description' => 'ตรวจสอบข้อมูลที่ผู้สนใจส่งผ่านแบบฟอร์ม ติดต่อกลับ และส่งออก Report',
                    'icon' => 'fas fa-inbox',
                    'tone' => 'purple',
                    'items' => [
                        ['label' => 'เปิดกล่องข้อความ', 'count' => DB::table('contact_enquiries')->count(), 'route' => route('admin.contact-enquiries')],
                    ],
                ],
            ],
        ]);
    }

    public function projects(string $type): View
    {
        $sections = [
            'ppa' => ['title' => __('admin.ppa'), 'icon' => 'fas fa-solar-panel'],
            'epc' => ['title' => __('admin.epc'), 'icon' => 'fas fa-hard-hat'],
            'ev' => ['title' => __('admin.ev'), 'icon' => 'fas fa-charging-station'],
        ];

        return view('admin.section', [...$sections[$type], 'kind' => 'projects', 'type' => $type, 'records' => DB::table('projects')->where('type', $type)->orderBy('sort_order')->get()]);
    }

    public function websiteSettings(): View
    {
        return view('admin.section', ['title' => __('admin.website_settings'), 'icon' => 'fas fa-sliders-h', 'kind' => 'settings']);
    }

    public function careers(): View
    {
        return view('admin.section', ['title' => __('admin.careers'), 'icon' => 'fas fa-user-plus', 'kind' => 'careers']);
    }

    public function contactEnquiries(): View
    {
        return view('admin.section', ['title' => __('admin.enquiries'), 'icon' => 'fas fa-inbox', 'kind' => 'enquiries']);
    }
}
