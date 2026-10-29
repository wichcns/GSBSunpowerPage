<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = ['category', 'title_th', 'title_en', 'title_zh', 'excerpt_th', 'excerpt_en', 'excerpt_zh', 'content_th', 'content_en', 'content_zh', 'cover_image', 'cover_image_th', 'cover_image_en', 'cover_image_zh', 'published_at', 'sort_order', 'is_published'];

    protected $casts = ['published_at' => 'date', 'is_published' => 'boolean'];
}
