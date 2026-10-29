<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogVideo extends Model
{
    protected $fillable = ['title_th', 'title_en', 'title_zh', 'video_url', 'thumbnail_image', 'sort_order', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
