<?php

namespace App\Crm\Actions;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Services\CrmLineNotifier;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveSite
{
    private const FIELDS = ['name', 'area', 'location_type', 'map_url', 'owner_user_id', 'source', 'notes'];

    public function __construct(private readonly CrmLineNotifier $notifier) {}

    public function create(array $data, User $actor): Site
    {
        return DB::transaction(function () use ($data, $actor) {
            $site = Site::create(collect($data)->only(self::FIELDS)->all());
            $site->update(['site_code' => sprintf('SITE-%06d', $site->id)]);
            $this->saveContacts($site, $data['contacts'] ?? []);
            $audit = $site->audits()->create([
                'actor_user_id' => $actor->id,
                'action' => 'created',
                'changed_fields' => array_keys($data),
            ]);
            if ($site->owner_user_id !== null) {
                $this->notifier->assignment($site, User::findOrFail($site->owner_user_id), $actor, $audit->id);
            }

            return $site;
        });
    }

    public function update(Site $site, array $data, User $actor): Site
    {
        return DB::transaction(function () use ($site, $data, $actor) {
            $site = Site::query()->with('contacts')->lockForUpdate()->findOrFail($site->getKey());
            $oldOwnerId = $site->owner_user_id;
            if (! hash_equals(SiteEditFingerprint::for($site), $data['site_fingerprint'])) {
                throw ValidationException::withMessages([
                    'site_fingerprint' => 'ข้อมูลสถานที่หรือผู้ติดต่อมีการเปลี่ยนแปลงแล้ว กรุณาโหลดข้อมูลล่าสุดก่อนบันทึก',
                ]);
            }
            $site->fill(collect($data)->only(self::FIELDS)->all());
            $changed = array_keys($site->getDirty());
            $site->save();

            $deleted = $site->contacts()->whereIn('id', $data['deleted_contact_ids'] ?? [])->delete();
            if ($deleted > 0) {
                $changed[] = 'contacts';
            }

            if (array_key_exists('contacts', $data)) {
                $changed = array_merge($changed, $this->saveContacts($site, $data['contacts']));
            }

            if ($deleted > 0) {
                $this->ensurePrimaryContact($site);
            }

            if ($changed !== []) {
                $audit = $site->audits()->create([
                    'actor_user_id' => $actor->id,
                    'action' => 'updated',
                    'changed_fields' => array_values(array_unique($changed)),
                ]);
                if (in_array('owner_user_id', $changed, true) && $site->owner_user_id !== null && $site->owner_user_id !== $oldOwnerId) {
                    $this->notifier->assignment($site, User::findOrFail($site->owner_user_id), $actor, $audit->id);
                }
            }

            return $site;
        });
    }

    private function saveContacts(Site $site, array $contacts): array
    {
        if (collect($contacts)->contains(fn ($contact) => (bool) ($contact['is_primary'] ?? false))) {
            $site->contacts()->update(['is_primary' => false]);
        }

        $changed = [];
        foreach ($contacts as $contactData) {
            $contact = isset($contactData['id'])
                ? $site->contacts()->findOrFail($contactData['id'])
                : $site->contacts()->make();
            $contact->fill(collect($contactData)->only(['name', 'role', 'phone', 'line_name', 'email', 'is_primary'])->all());
            if ($contact->isDirty()) {
                $changed[] = 'contacts';
            }
            $contact->save();
        }

        return $changed;
    }

    private function ensurePrimaryContact(Site $site): void
    {
        if (! $site->contacts()->where('is_primary', true)->exists()) {
            $site->contacts()->orderBy('id')->first()?->update(['is_primary' => true]);
        }
    }
}
