<?php

namespace App\Crm\Http\Requests;

use App\Crm\Support\LocationTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }
        if ($user->role?->slug === 'staff') {
            $site = $this->route('site');

            return $site === null || $site->owner_user_id === $user->id;
        }

        return $user->hasPermission('sites.write');
    }

    public function rules(): array
    {
        $site = $this->route('site');

        return [
            'site_fingerprint' => $site ? ['required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/'] : ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'location_type' => ['nullable', Rule::in($site && $site->location_type ? [...LocationTypes::codes(), $site->location_type] : LocationTypes::codes())],
            'map_url' => ['nullable', 'url:http,https', 'max:2048'],
            'owner_user_id' => ['nullable', 'integer', 'exists:crm_users,id'],
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'contacts' => ['sometimes', 'array'],
            'contacts.*.id' => $site ? ['nullable', 'integer', Rule::exists('crm_contacts', 'id')->where('site_id', $site->id)] : ['prohibited'],
            'contacts.*.name' => ['required', 'string', 'max:255'],
            'contacts.*.role' => ['nullable', 'string', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:100'],
            'contacts.*.line_name' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.is_primary' => ['sometimes', 'boolean'],
            'deleted_contact_ids' => $site ? ['sometimes', 'array'] : ['prohibited'],
            'deleted_contact_ids.*' => $site ? ['integer', Rule::exists('crm_contacts', 'id')->where('site_id', $site->id)] : ['prohibited'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $primaryCount = collect($this->input('contacts', []))->filter(fn ($contact) => filter_var($contact['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN))->count();

            if ($primaryCount > 1) {
                $validator->errors()->add('contacts', 'Select only one primary contact.');
            }
        });
    }
}
