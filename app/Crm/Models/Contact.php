<?php

namespace App\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'crm_contacts';

    protected $fillable = ['site_id', 'name', 'role', 'phone', 'line_name', 'email', 'is_primary', 'legacy_lead_id'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }
}
