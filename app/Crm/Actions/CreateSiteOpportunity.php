<?php

namespace App\Crm\Actions;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Support\Facades\DB;

class CreateSiteOpportunity
{
    public function execute(Site $site, User $actor): string
    {
        return DB::transaction(function () use ($site, $actor): string {
            $site = Site::query()->lockForUpdate()->findOrFail($site->id);
            if ($site->legacy_lead_id !== null) {
                return $site->legacy_lead_id;
            }

            $sourceId = 'native-site-'.$site->id;
            DB::table('crm_native_opportunities')->insert([
                'site_id' => $site->id,
                'source_id' => $sourceId,
                'payload' => json_encode([
                    'id' => $sourceId,
                    'site_name' => $site->name,
                    'stage' => 'NEW',
                    'priority' => 'medium',
                    'probability' => 10,
                ], JSON_THROW_ON_ERROR),
                'created_by_user_id' => $actor->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $site->update(['legacy_lead_id' => $sourceId]);
            $site->audits()->create([
                'actor_user_id' => $actor->id,
                'action' => 'opportunity_created',
                'changed_fields' => ['legacy_lead_id'],
            ]);

            return $sourceId;
        });
    }
}
