<?php

namespace App\Crm\Http\Requests;

use App\Crm\Support\LocationTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaveUnifiedLeadRequest extends FormRequest
{
    public const PROFILE = ['site_name', 'area', 'location_type', 'lead_source', 'map_url', 'assigned_to',
        'contact_name', 'contact_role', 'phone', 'line_name', 'email', 'appointment_status', 'next_action', 'next_action_at'];

    public const TECHNICAL = ['power_authority', 'meter_status', 'transformer_owner', 'transformer_kva',
        'transformer_cost', 'requested_chargers', 'requested_connectors', 'charger_power_kw',
        'installation_status', 'low_priority_status'];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $sourceId = $this->route('sourceId');
        $siteType = DB::table('crm_sites')->where('legacy_lead_id', $sourceId)->value('location_type');
        $currentProfile = DB::table('crm_lead_details')->where('lead_source_id', $sourceId)->value('profile');
        $currentType = $currentProfile ? (json_decode($currentProfile, true)['location_type'] ?? null) : null;
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $legacyPayload = DB::table('crm_legacy_records')->where('batch_id', $batchId)->where('source_table', 'leads')->where('source_id', $sourceId)->value('payload');
        $legacyType = $legacyPayload ? (json_decode($legacyPayload, true)['location_type'] ?? null) : null;
        $allowedTypes = array_values(array_unique(array_filter([...LocationTypes::codes(), $siteType, $currentType, $legacyType])));
        $rules = [
            'version' => ['present', 'nullable', 'integer', 'min:1'],
            'site_fingerprint' => ['present', 'nullable', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/'],
            'business_version' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'profile' => ['required', 'array:'.implode(',', self::PROFILE), 'required_array_keys:'.implode(',', self::PROFILE)],
            'profile.site_name' => ['required', 'string', 'max:255'],
            'profile.email' => ['present', 'nullable', 'email', 'max:255'],
            'profile.map_url' => ['present', 'nullable', 'url:http,https', 'max:2048'],
            'profile.location_type' => ['present', 'nullable', Rule::in($allowedTypes)],
            'profile.next_action_at' => ['present', 'nullable', 'date'],
            'business' => ['required', 'array:'.implode(',', SaveCrmBusinessTermsRequest::FIELDS),
                'required_array_keys:'.implode(',', array_diff(SaveCrmBusinessTermsRequest::FIELDS, SaveCrmBusinessTermsRequest::NEW_CONTRACT_FIELDS))],
            'business.commercial_model' => ['present', 'nullable', Rule::in(SaveCrmBusinessTermsRequest::COMMERCIAL_MODELS)],
            'business.investment_type' => ['present', 'nullable', Rule::in(['GSB', 'JOINT', 'CUSTOMER', 'sharing'])],
            'business.revenue_share_unit' => ['present', 'nullable', Rule::in(['THB_KWH', 'PERCENT'])],
            'business.contract_expiry' => ['present', 'nullable', 'date_format:Y-m-d'],
            'business.parking_spaces' => ['present', 'nullable', 'integer', 'min:0'],
            'business.contract_signed_at' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'business.contract_effective_at' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'business.monthly_payment_day' => ['sometimes', 'nullable', 'integer', 'between:1,31'],
            'technical' => ['required', 'array:'.implode(',', self::TECHNICAL),
                'required_array_keys:'.implode(',', self::TECHNICAL)],
        ];
        foreach (array_diff(self::PROFILE, ['site_name', 'email', 'map_url', 'next_action_at', 'location_type']) as $field) {
            $rules['profile.'.$field] = ['present', 'nullable', 'string', 'max:'.($field === 'next_action' ? 10000 : 500)];
        }
        foreach (['rental_cost', 'revenue_share_amount', 'deposit_amount'] as $field) {
            $rules['business.'.$field] = ['present', 'nullable', 'numeric', 'min:0'];
        }
        foreach (['gsb_investment_percent', 'partner_investment_percent'] as $field) {
            $rules['business.'.$field] = ['present', 'nullable', 'numeric', 'between:0,100'];
        }
        foreach (['rental_terms', 'rental_unit', 'deposit_months', 'contract_status', 'contract_term', 'rent_free_months', 'notes'] as $field) {
            $rules['business.'.$field] = ['present', 'nullable', 'string', 'max:'.($field === 'notes' ? 10000 : 255)];
        }
        foreach (['transformer_kva', 'transformer_cost', 'charger_power_kw'] as $field) {
            $rules['technical.'.$field] = ['present', 'nullable', 'numeric', 'min:0'];
        }
        foreach (['requested_chargers', 'requested_connectors'] as $field) {
            $rules['technical.'.$field] = ['present', 'nullable', 'integer', 'min:0'];
        }
        foreach (array_diff(self::TECHNICAL, ['transformer_kva', 'transformer_cost', 'charger_power_kw', 'requested_chargers', 'requested_connectors']) as $field) {
            $rules['technical.'.$field] = ['present', 'nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $profile = $this->input('profile');
            if (is_array($profile) && ! filled($profile['contact_name'] ?? null)) {
                $hasOtherContactData = collect(['contact_role', 'phone', 'line_name', 'email'])
                    ->contains(fn ($field) => filled($profile[$field] ?? null));
                $siteId = DB::table('crm_sites')->where('legacy_lead_id', $this->route('sourceId'))->value('id');
                $hasSavedContact = $siteId !== null && DB::table('crm_contacts')->where('site_id', $siteId)->exists();
                if ($hasOtherContactData || $hasSavedContact) {
                    $validator->errors()->add('profile.contact_name', 'กรุณาระบุชื่อผู้ติดต่อก่อนบันทึก');
                }
            }
            $business = $this->input('business');
            if (! is_array($business) || ($business['investment_type'] ?? null) !== 'JOINT') {
                return;
            }
            $gsb = $business['gsb_investment_percent'] ?? null;
            $partner = $business['partner_investment_percent'] ?? null;
            if (! is_numeric($gsb) || ! is_numeric($partner) || abs((float) $gsb + (float) $partner - 100) > 0.00001) {
                $validator->errors()->add('business.partner_investment_percent', 'การร่วมลงทุนต้องระบุสัดส่วนทั้งสองฝ่ายรวมกัน 100%');
            }
        });
    }
}
