<?php

namespace App\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteAudit extends Model
{
    protected $table = 'crm_site_audits';

    protected $fillable = ['site_id', 'actor_user_id', 'action', 'changed_fields'];

    protected function casts(): array
    {
        return ['changed_fields' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
