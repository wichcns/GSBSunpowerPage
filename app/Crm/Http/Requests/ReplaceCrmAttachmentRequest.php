<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceCrmAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['file' => StoreCrmAttachmentRequest::fileRules()];
    }
}
