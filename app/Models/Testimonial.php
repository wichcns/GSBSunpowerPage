<?php

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    protected $fillable = ['name', 'company', 'quote_th', 'quote_en', 'quote_zh', 'rating', 'image_path', 'sort_order', 'is_published'];
}
