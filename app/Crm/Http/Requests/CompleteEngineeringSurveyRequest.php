<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteEngineeringSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'result_summary' => ['required', 'string', 'min:3', 'max:5000'],
            'expected_version' => ['required', 'string', 'max:150'],
            'expected_project_version' => ['required', 'string', 'max:150'],
        ];
    }
}
