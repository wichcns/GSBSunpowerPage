<?php

namespace App\Crm\Support;

use App\Crm\Models\Site;

class SiteEditFingerprint
{
    public static function for(Site $site): string
    {
        $site->loadMissing('contacts');

        return hash('sha256', json_encode([
            'site' => $site->only([
                'id', 'site_code', 'name', 'area', 'location_type', 'map_url',
                'owner_user_id', 'source', 'notes', 'legacy_lead_id', 'legacy_owner_label',
            ]),
            'contacts' => $site->contacts->sortBy('id')->values()->map(fn ($contact) => $contact->only([
                'id', 'name', 'role', 'phone', 'line_name', 'email', 'is_primary', 'legacy_lead_id',
            ]))->all(),
        ], JSON_THROW_ON_ERROR));
    }
}
