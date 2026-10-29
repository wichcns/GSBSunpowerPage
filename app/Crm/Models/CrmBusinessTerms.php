<?php

namespace App\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmBusinessTerms extends Model
{
    protected $table = 'crm_business_terms';

    protected $fillable = ['lead_source_id', 'values', 'version', 'updated_by_user_id'];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected function casts(): array
    {
        return ['values' => 'array', 'version' => 'integer'];
    }
}
