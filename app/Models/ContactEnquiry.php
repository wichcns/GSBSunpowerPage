<?php

namespace App\Models;

use Database\Factories\ContactEnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    /** @use HasFactory<ContactEnquiryFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'company', 'email', 'phone', 'services', 'budget', 'project_location',
        'website', 'message', 'source', 'status',
    ];

    protected function casts(): array
    {
        return ['services' => 'array'];
    }
}
