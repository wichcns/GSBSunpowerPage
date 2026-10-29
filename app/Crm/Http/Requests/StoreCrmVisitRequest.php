<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'visit_type' => ['required', Rule::in(['FOLLOW_UP', 'MEETING', 'SITE_VISIT', 'SITE_SURVEY', 'NEGOTIATION'])],
            'occurred_at' => ['required', 'date'],
            'internal_participants' => ['required', 'string', 'max:500'],
            'customer_participants' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:500'],
            'summary' => ['required', 'string', 'max:10000'],
            'site_findings' => ['nullable', 'string', 'max:10000'],
            'decisions' => ['nullable', 'string', 'max:10000'],
            'risks' => ['nullable', 'string', 'max:10000'],
            'next_steps' => ['required', 'string', 'max:10000'],
            'next_action_at' => ['nullable', 'date'],
        ];
    }
}
