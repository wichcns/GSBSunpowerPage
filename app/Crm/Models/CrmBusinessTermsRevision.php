<?php

namespace App\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class CrmBusinessTermsRevision extends Model
{
    public $timestamps = false;

    protected $table = 'crm_business_terms_revisions';

    protected $fillable = ['lead_source_id', 'version', 'changed_by_user_id', 'changes', 'created_at'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'version' => 'integer', 'created_at' => 'datetime'];
    }
}
