<?php

namespace App\Crm\Actions;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Services\CrmLineNotifier;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignSiteOwners
{
    public function __construct(private readonly CrmLineNotifier $notifier) {}

    public function assign(array $siteIds, array $fingerprints, int $ownerId, User $actor): int
    {
        return DB::transaction(function () use ($siteIds, $fingerprints, $ownerId, $actor): int {
            $sites = Site::query()->with('contacts')->whereIn('id', $siteIds)
                ->orderBy('id')->lockForUpdate()->get();

            if ($sites->count() !== count($siteIds)) {
                throw ValidationException::withMessages(['site_ids' => __('crm_errors.site_not_found')]);
            }

            foreach ($sites as $site) {
                $submitted = $fingerprints[$site->id] ?? null;
                if ($site->owner_user_id !== null || ! is_string($submitted)
                    || ! hash_equals(SiteEditFingerprint::for($site), $submitted)) {
                    throw ValidationException::withMessages(['site_ids' => __('crm_errors.site_assignment_changed')]);
                }
            }

            foreach ($sites as $site) {
                $site->owner_user_id = $ownerId;
                $site->save();
                $audit = $site->audits()->create([
                    'actor_user_id' => $actor->id,
                    'action' => 'owner_assigned',
                    'changed_fields' => ['owner_user_id'],
                ]);
                $this->notifier->assignment($site, User::findOrFail($ownerId), $actor, $audit->id);
            }

            return $sites->count();
        });
    }
}
