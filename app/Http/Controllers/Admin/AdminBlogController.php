<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBlogController extends Controller
{
    public function index(): View
    {
        return view('admin.blog.index', ['posts' => BlogPost::query()->orderBy('sort_order')->get(), 'videos' => BlogVideo::query()->orderBy('sort_order')->get()]);
    }

    public function createPost(): View
    {
        return view('admin.blog.post-form', ['post' => new BlogPost]);
    }

    public function editPost(BlogPost $post): View
    {
        return view('admin.blog.post-form', compact('post'));
    }

    public function storePost(Request $request): RedirectResponse
    {
        $post = BlogPost::create($this->postData($request));

        return redirect()->route('admin.blog.posts.edit', $post)->with('success', 'สร้างบทความเรียบร้อยแล้ว');
    }

    public function updatePost(Request $request, BlogPost $post): RedirectResponse
    {
        $post->update($this->postData($request, $post));

        return redirect()->route('admin.blog.index')->with('success', 'บันทึกบทความเรียบร้อยแล้ว');
    }

    public function destroyPost(BlogPost $post): RedirectResponse
    {
        $post->delete();

        return back()->with('success', 'ลบบทความเรียบร้อยแล้ว');
    }

    public function createVideo(): View
    {
        return view('admin.blog.video-form', ['video' => new BlogVideo]);
    }

    public function editVideo(BlogVideo $video): View
    {
        return view('admin.blog.video-form', compact('video'));
    }

    public function storeVideo(Request $request): RedirectResponse
    {
        $video = BlogVideo::create($this->videoData($request));

        return redirect()->route('admin.blog.videos.edit', $video)->with('success', 'เพิ่มวิดีโอเรียบร้อยแล้ว');
    }

    public function updateVideo(Request $request, BlogVideo $video): RedirectResponse
    {
        $video->update($this->videoData($request, $video));

        return redirect()->route('admin.blog.index')->with('success', 'บันทึกวิดีโอเรียบร้อยแล้ว');
    }

    public function destroyVideo(BlogVideo $video): RedirectResponse
    {
        $video->delete();

        return back()->with('success', 'ลบวิดีโอเรียบร้อยแล้ว');
    }

    private function postData(Request $request, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'category' => ['required', 'in:solar,ev,business'],
            'title_th' => ['nullable', 'string', 'max:220'],
            'title_en' => ['nullable', 'string', 'max:220'],
            'title_zh' => ['nullable', 'string', 'max:220'],
            'excerpt_th' => ['nullable', 'string'],
            'excerpt_en' => ['nullable', 'string'],
            'excerpt_zh' => ['nullable', 'string'],
            'content_th' => ['nullable', 'string'],
            'content_en' => ['nullable', 'string'],
            'content_zh' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'cover_image_th' => ['nullable', 'image', 'max:5120'],
            'cover_image_en' => ['nullable', 'image', 'max:5120'],
            'cover_image_zh' => ['nullable', 'image', 'max:5120'],
        ]);
        $data['is_published'] = $request->boolean('is_published');

        foreach (['th', 'en', 'zh'] as $locale) {
            $field = "cover_image_{$locale}";
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('uploads/blog', 'public');
            } else {
                unset($data[$field]);
            }
        }

        return $data;
    }

    private function videoData(Request $request, ?BlogVideo $video = null): array
    {
        $data = $request->validate(['title_th' => ['nullable', 'string', 'max:220'], 'title_en' => ['nullable', 'string', 'max:220'], 'title_zh' => ['nullable', 'string', 'max:220'], 'video_url' => ['required', 'string', 'max:1000'], 'sort_order' => ['required', 'integer', 'min:1'], 'thumbnail_image' => ['nullable', 'image', 'max:5120']]);
        $data['is_published'] = $request->boolean('is_published');
        if ($request->hasFile('thumbnail_image')) {
            $data['thumbnail_image'] = $request->file('thumbnail_image')->store('uploads/blog', 'public');
        } else {
            unset($data['thumbnail_image']);
        }

return $data;
    }
}
