<?php

namespace App\Crm\Support;

use App\Crm\Models\Site;

class LeadSiteFingerprint
{
    public static function for(?Site $site): ?string
    {
        if ($site === null) {
            return null;
        }
        $site->loadMissing('contacts');
        $contact = $site->contacts->sortBy('id')->firstWhere('is_primary', true)
            ?? $site->contacts->sortBy('id')->first();

        return hash('sha256', json_encode([
            'site' => $site->only(['id', 'name', 'area', 'location_type', 'map_url', 'source', 'owner_user_id', 'legacy_owner_label']),
            'contact' => $contact?->only(['id', 'name', 'role', 'phone', 'line_name', 'email']),
        ], JSON_THROW_ON_ERROR));
    }
}
