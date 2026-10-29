<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogVideo;
use App\Support\PublishedBlogContent;
use Illuminate\View\View;

class BlogPageController extends Controller
{
    public function index(PublishedBlogContent $publishedBlogContent): View
    {
        abort_unless($publishedBlogContent->isAvailable(), 404);

        return view('pages.insights', [
            'posts' => BlogPost::query()->where('is_published', true)->orderBy('sort_order')->orderByDesc('published_at')->get(),
            'videos' => BlogVideo::query()->where('is_published', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->is_published, 404);

        return view('pages.blog-detail', [
            'post' => $post,
        ]);
    }
}
