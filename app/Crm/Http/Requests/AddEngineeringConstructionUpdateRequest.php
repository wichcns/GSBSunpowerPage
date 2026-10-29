<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddEngineeringConstructionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'min:3', 'max:4000'],
            'expected_version' => ['required', 'string', 'max:150'],
        ];
    }
}
