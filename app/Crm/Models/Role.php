<?php

namespace App\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'crm_roles';

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }
}
