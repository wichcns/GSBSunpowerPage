<?php

namespace App\Crm\Http\Requests;

use App\Crm\Support\EngineeringStages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEngineeringStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'engineering_stage' => ['required', 'string', Rule::in(EngineeringStages::allowed())],
            'expected_version' => ['required', 'string', 'max:100'],
        ];
    }
}
