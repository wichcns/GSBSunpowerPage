<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['SITE_PHOTO', 'SITE_SURVEY', 'PROPOSAL', 'CONTRACT', 'ELECTRICITY', 'COMPANY_DOC', 'OTHER'])],
            'note' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:20480',
                'extensions:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx,ppt,pptx'],
        ];
    }
}
