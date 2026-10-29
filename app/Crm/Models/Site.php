<?php

namespace App\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $table = 'crm_sites';

    protected $fillable = ['site_code', 'name', 'area', 'location_type', 'map_url', 'owner_user_id', 'source', 'notes', 'legacy_lead_id', 'legacy_owner_label'];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SiteAudit::class)->latest();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
