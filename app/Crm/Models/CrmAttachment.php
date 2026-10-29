<?php

namespace App\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAttachment extends Model
{
    protected $table = 'crm_attachments';

    protected $guarded = ['id'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
