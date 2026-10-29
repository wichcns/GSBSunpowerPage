<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTestimonialController extends Controller
{
    public function index(): View
    {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::query()->orderBy('sort_order')->get()]);
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'company' => ['nullable', 'string', 'max:160'], 'quote_th' => ['nullable', 'string', 'max:3000'], 'quote_en' => ['nullable', 'string', 'max:3000'], 'quote_zh' => ['nullable', 'string', 'max:3000'], 'rating' => ['required', 'integer', 'between:1,5'], 'sort_order' => ['required', 'integer', 'min:1'], 'is_published' => ['nullable', 'boolean']]);
        $data['is_published'] = $request->boolean('is_published');
        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'บันทึกรีวิวเรียบร้อยแล้ว');
    }
}
