<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\BlogVideo;

class PublishedBlogContent
{
    /**
     * A Blog becomes public as soon as at least one article or video is published.
     */
    public function isAvailable(): bool
    {
        return BlogPost::query()->where('is_published', true)->exists()
            || BlogVideo::query()->where('is_published', true)->exists();
    }
}
