<?php

namespace App\Models;

use Database\Factories\SolutionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    /** @use HasFactory<SolutionFactory> */
    use HasFactory;

    protected $fillable = [
        'slug', 'title_en', 'title_th', 'title_zh', 'summary_en', 'summary_th', 'summary_zh',
        'body_en', 'body_th', 'body_zh', 'image_path', 'destination_url', 'sort_order', 'is_published',
    ];
}
