<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEngineeringConstructionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'owner_user_id' => ['required', 'integer', Rule::exists('crm_users', 'id')],
            'started_on' => ['required', 'date_format:Y-m-d'],
            'target_finish_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:started_on'],
            'expected_version' => ['nullable', 'string', 'max:150'],
            'expected_project_version' => ['required', 'string', 'max:150'],
        ];
    }
}
