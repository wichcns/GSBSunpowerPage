<?php

namespace App\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCrmBusinessTermsRequest extends FormRequest
{
    public const FIELDS = [
        'commercial_model', 'investment_type', 'rental_cost', 'rental_terms', 'rental_unit',
        'revenue_share_amount', 'revenue_share_unit', 'parking_spaces', 'deposit_amount',
        'deposit_months', 'gsb_investment_percent', 'partner_investment_percent',
        'contract_status', 'contract_term', 'contract_expiry', 'rent_free_months', 'contract_signed_at', 'contract_effective_at', 'monthly_payment_day', 'notes',
    ];

    public const NEW_CONTRACT_FIELDS = ['contract_signed_at', 'contract_effective_at', 'monthly_payment_day'];

    public const COMMERCIAL_MODELS = ['RENT', 'PARKING_RENT', 'AREA_RENT', 'REVENUE_SHARE', 'RENT_AND_SHARE'];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'version' => ['present', 'nullable', 'integer', 'min:1'],
            'values' => ['required', 'array:'.implode(',', self::FIELDS), 'required_array_keys:'.implode(',', array_diff(self::FIELDS, self::NEW_CONTRACT_FIELDS))],
            'values.commercial_model' => ['present', 'nullable', Rule::in(self::COMMERCIAL_MODELS)],
            'values.investment_type' => ['present', 'nullable', Rule::in(['GSB', 'JOINT', 'CUSTOMER', 'sharing'])],
            'values.revenue_share_unit' => ['present', 'nullable', Rule::in(['THB_KWH', 'PERCENT'])],
            'values.contract_expiry' => ['present', 'nullable', 'date_format:Y-m-d'],
            'values.parking_spaces' => ['present', 'nullable', 'integer', 'min:0'],
            'values.contract_signed_at' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'values.contract_effective_at' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'values.monthly_payment_day' => ['sometimes', 'nullable', 'integer', 'between:1,31'],
        ];
        foreach (['rental_cost', 'revenue_share_amount', 'deposit_amount'] as $field) {
            $rules['values.'.$field] = ['present', 'nullable', 'numeric', 'min:0'];
        }
        foreach (['gsb_investment_percent', 'partner_investment_percent'] as $field) {
            $rules['values.'.$field] = ['present', 'nullable', 'numeric', 'between:0,100'];
        }
        foreach (['rental_terms', 'rental_unit', 'deposit_months', 'contract_status', 'contract_term', 'rent_free_months'] as $field) {
            $rules['values.'.$field] = ['present', 'nullable', 'string', 'max:255'];
        }
        $rules['values.notes'] = ['present', 'nullable', 'string', 'max:10000'];

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $values = $this->input('values');
            if (! is_array($values) || ($values['investment_type'] ?? null) !== 'JOINT') {
                return;
            }
            $gsb = $values['gsb_investment_percent'] ?? null;
            $partner = $values['partner_investment_percent'] ?? null;
            if (! is_numeric($gsb) || ! is_numeric($partner) || abs((float) $gsb + (float) $partner - 100) > 0.00001) {
                $validator->errors()->add('values.partner_investment_percent', __('crm_errors.investment_total'));
            }
        });
    }
}
