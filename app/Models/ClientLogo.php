<?php

namespace App\Models;

use Database\Factories\ClientLogoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLogo extends Model
{
    /** @use HasFactory<ClientLogoFactory> */
    use HasFactory;

    protected $fillable = ['name', 'image_path', 'alt_text', 'rail', 'sort_order', 'is_published'];
}
