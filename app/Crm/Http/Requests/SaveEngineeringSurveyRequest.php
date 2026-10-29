<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEngineeringSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'assignee_user_id' => ['nullable', 'integer', Rule::exists('crm_users', 'id')],
            'scheduled_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'expected_version' => ['required', 'string', 'max:150'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $hasAssignee = $this->filled('assignee_user_id');
            $hasDate = $this->filled('scheduled_at');
            if ($hasAssignee !== $hasDate) {
                $validator->errors()->add('scheduled_at', 'กรุณาระบุทั้งผู้รับผิดชอบและวันนัดสำรวจ');
            }
        });
    }
}
