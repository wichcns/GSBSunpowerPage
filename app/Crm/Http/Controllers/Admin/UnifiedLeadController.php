<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\SaveCrmBusinessTermsRequest;
use App\Crm\Http\Requests\SaveUnifiedLeadRequest;
use App\Crm\Models\Contact;
use App\Crm\Models\Site;
use App\Crm\Support\LeadSiteFingerprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UnifiedLeadController extends Controller
{
    public function update(string $sourceId, SaveUnifiedLeadRequest $request): JsonResponse
    {
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        $record = $batchId === null ? null : DB::table('crm_legacy_records')
            ->where('batch_id', $batchId)->where('source_table', 'leads')->where('source_id', $sourceId)->first(['payload']);
        abort_if($record === null, 404);
        $legacy = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
        $data = $request->validated();

        $result = DB::transaction(function () use ($sourceId, $batchId, $legacy, $data, $request): array {
            // The lead row exists even before the first current-detail save, so it serializes first writers.
            DB::table('crm_legacy_records')->where('batch_id', $batchId)
                ->where('source_table', 'leads')->where('source_id', $sourceId)->lockForUpdate()->first();
            $now = now();
            $userId = $request->user()->id;
            $current = DB::table('crm_lead_details')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            $terms = DB::table('crm_business_terms')->where('lead_source_id', $sourceId)->lockForUpdate()->first();
            if (($current?->version) !== $data['version'] || ($terms?->version) !== ($data['business_version'] ?? null)) {
                abort(409, 'ข้อมูลนี้มีการเปลี่ยนแปลงแล้ว กรุณาโหลดข้อมูลล่าสุด');
            }

            $baselineProfile = $current === null
                ? $this->pick($legacy, SaveUnifiedLeadRequest::PROFILE)
                : json_decode($current->profile, true, 512, JSON_THROW_ON_ERROR);
            $site = Site::query()->with(['owner:id,name', 'contacts'])->where('legacy_lead_id', $sourceId)
                ->lockForUpdate()->first();
            if ($site !== null) {
                $site->setRelation('contacts', Contact::query()->where('site_id', $site->id)
                    ->orderBy('id')->lockForUpdate()->get());
            }
            if (LeadSiteFingerprint::for($site) !== $data['site_fingerprint']) {
                abort(409, 'ข้อมูลสถานที่หรือผู้ติดต่อเปลี่ยนแล้ว กรุณาโหลดข้อมูลล่าสุด');
            }
            if ($site?->owner_user_id !== null && $data['profile']['assigned_to'] !== $site->owner?->name) {
                throw ValidationException::withMessages([
                    'profile.assigned_to' => 'สถานที่นี้ผูกผู้รับผิดชอบกับบัญชีผู้ใช้แล้ว กรุณาเปลี่ยนผ่านหน้าสถานที่และลูกค้า',
                ]);
            }
            if ($site !== null) {
                $baselineProfile['site_name'] = $site->name;
                $baselineProfile['area'] = $site->area;
                $baselineProfile['location_type'] = $site->location_type;
                $baselineProfile['map_url'] = $site->map_url;
                $baselineProfile['lead_source'] = $site->source ?? $baselineProfile['lead_source'];
                $baselineProfile['assigned_to'] = $site->owner?->name ?? $site->legacy_owner_label ?? $baselineProfile['assigned_to'];
                $contact = $site->contacts->firstWhere('is_primary', true) ?? $site->contacts->first();
                if ($contact !== null) {
                    foreach (['contact_name' => 'name', 'contact_role' => 'role', 'phone' => 'phone',
                        'line_name' => 'line_name', 'email' => 'email'] as $profileField => $contactField) {
                        $baselineProfile[$profileField] = $contact->{$contactField};
                    }
                }
            }
            $baselineTechnical = $current === null
                ? $this->pick($legacy, SaveUnifiedLeadRequest::TECHNICAL)
                : json_decode($current->technical, true, 512, JSON_THROW_ON_ERROR);
            $baselineBusiness = $terms === null
                ? $this->pick($legacy, SaveCrmBusinessTermsRequest::FIELDS)
                : json_decode($terms->values, true, 512, JSON_THROW_ON_ERROR);
            $changes = [];
            foreach (['profile' => $baselineProfile, 'business' => $baselineBusiness, 'technical' => $baselineTechnical] as $section => $before) {
                foreach ($data[$section] as $field => $value) {
                    if (($before[$field] ?? null) !== $value) {
                        $changes[$section][$field] = ['before' => $before[$field] ?? null, 'after' => $value];
                    }
                }
            }
            if ($changes === []) {
                return ['version' => $current?->version, 'business_version' => $terms?->version,
                    'site_fingerprint' => LeadSiteFingerprint::for($site), 'business_changed' => false, 'changed' => false];
            }

            $version = ($current?->version ?? 0) + 1;
            DB::table('crm_lead_details')->updateOrInsert(['lead_source_id' => $sourceId], [
                'profile' => json_encode($data['profile'], JSON_THROW_ON_ERROR),
                'technical' => json_encode($data['technical'], JSON_THROW_ON_ERROR),
                'version' => $version, 'updated_by_user_id' => $userId,
                'created_at' => $current?->created_at ?? $now, 'updated_at' => $now,
            ]);
            DB::table('crm_lead_detail_revisions')->insert([
                'lead_source_id' => $sourceId, 'version' => $version, 'changed_by_user_id' => $userId,
                'changes' => json_encode($changes, JSON_THROW_ON_ERROR), 'created_at' => $now,
            ]);
            $this->syncSite($sourceId, $data['profile'], $userId);

            $businessVersion = $terms?->version;
            if (isset($changes['business'])) {
                $businessVersion = ($terms?->version ?? 0) + 1;
                DB::table('crm_business_terms')->updateOrInsert(['lead_source_id' => $sourceId], [
                    'values' => json_encode($data['business'], JSON_THROW_ON_ERROR),
                    'version' => $businessVersion, 'updated_by_user_id' => $userId,
                    'created_at' => $terms?->created_at ?? $now, 'updated_at' => $now,
                ]);
                DB::table('crm_business_terms_revisions')->insert([
                    'lead_source_id' => $sourceId, 'version' => $businessVersion,
                    'changed_by_user_id' => $userId, 'changes' => json_encode($changes['business'], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                ]);
            }

            return ['version' => $version, 'business_version' => $businessVersion,
                'site_fingerprint' => LeadSiteFingerprint::for(Site::query()->with('contacts')->where('legacy_lead_id', $sourceId)->first()),
                'updated_by' => $request->user()->name, 'updated_at' => $now->toIso8601String(),
                'business_changed' => isset($changes['business']),
                'changed' => true];
        });

        return response()->json($result);
    }

    private function pick(array $source, array $fields): array
    {
        return array_replace(array_fill_keys($fields, null), array_intersect_key($source, array_flip($fields)));
    }

    private function syncSite(string $sourceId, array $profile, int $actorUserId): void
    {
        $site = Site::query()->where('legacy_lead_id', $sourceId)->lockForUpdate()->first();
        $created = $site === null;
        if ($site === null) {
            $site = Site::create(['legacy_lead_id' => $sourceId, 'name' => $profile['site_name']]);
            $site->update(['site_code' => sprintf('SITE-%06d', $site->id)]);
        }
        $site->fill([
            'name' => $profile['site_name'], 'area' => $profile['area'],
            'location_type' => $profile['location_type'], 'map_url' => $profile['map_url'],
            'source' => $profile['lead_source'], 'legacy_owner_label' => $profile['assigned_to'],
        ]);
        $changedFields = array_keys($site->getDirty());
        $site->save();
        $contact = $site->contacts()->where('is_primary', true)->first()
            ?? $site->contacts()->oldest('id')->first();
        $contactFields = ['name' => $profile['contact_name'], 'role' => $profile['contact_role'],
            'phone' => $profile['phone'], 'line_name' => $profile['line_name'], 'email' => $profile['email']];
        if ($contact !== null) {
            $contact->fill($contactFields);
            if ($contact->isDirty()) {
                $changedFields[] = 'contacts';
            }
            $contact->save();
        } elseif ($profile['contact_name'] !== null && $profile['contact_name'] !== '') {
            $site->contacts()->create($contactFields + ['is_primary' => true, 'legacy_lead_id' => $sourceId]);
            $changedFields[] = 'contacts';
        }
        if ($created || $changedFields !== []) {
            $site->audits()->create([
                'actor_user_id' => $actorUserId,
                'action' => $created ? 'created' : 'updated',
                'changed_fields' => array_values(array_unique($changedFields)),
            ]);
        }
    }
}
