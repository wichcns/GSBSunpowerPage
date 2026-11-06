<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\CrmAttachment;
use App\Crm\Models\CrmBusinessTerms;
use App\Crm\Models\CrmVisitLog;
use App\Crm\Models\Site;
use App\Crm\Support\LeadSiteFingerprint;
use Illuminate\Support\Facades\DB;
use JsonException;

class LegacyLeadDetail
{
    private const PROFILE_FIELDS = [
        'site_name',
        'area',
        'location_type',
        'lead_source',
        'map_url',
        'assigned_to',
        'contact_name',
        'contact_role',
        'phone',
        'line_name',
        'email',
        'appointment_status',
        'next_action',
        'next_action_at',
    ];

    private const BUSINESS_FIELDS = [
        'commercial_model',
        'investment_type',
        'rental_cost',
        'rental_terms',
        'rental_unit',
        'revenue_share_amount',
        'revenue_share_unit',
        'parking_spaces',
        'deposit_amount',
        'deposit_months',
        'gsb_investment_percent',
        'partner_investment_percent',
        'contract_status',
        'contract_term',
        'contract_expiry',
        'contract_signed_at',
        'contract_effective_at',
        'monthly_payment_day',
        'rent_free_months',
        'notes',
    ];

    private const TECHNICAL_FIELDS = [
        'power_authority',
        'meter_status',
        'transformer_owner',
        'transformer_kva',
        'transformer_cost',
        'requested_chargers',
        'requested_connectors',
        'charger_power_kw',
        'installation_status',
        'low_priority_status',
    ];

    private const RELATED_FIELDS = [
        'activities' => ['id', 'activity_type', 'summary', 'occurred_at'],
        'visit_logs' => [
            'id',
            'visit_type',
            'occurred_at',
            'internal_participants',
            'customer_participants',
            'location',
            'summary',
            'site_findings',
            'decisions',
            'risks',
            'next_steps',
            'next_action_at',
        ],
        'projects' => ['id', 'project_code', 'engineering_stage', 'sync_status', 'created_at', 'updated_at'],
        'attachments' => ['id', 'visit_log_id', 'category', 'file_name', 'mime_type', 'size_bytes', 'note', 'created_at'],
    ];

    public function __construct(private readonly LegacyPipeline $pipeline) {}

    public function find(string $sourceId): ?array
    {
        $batchId = DB::table('crm_legacy_import_batches')
            ->where('status', 'completed')->latest('id')->value('id');
        $leadRecord = $batchId === null ? null : DB::table('crm_legacy_records')
            ->where('batch_id', $batchId)->where('source_table', 'leads')
            ->where('source_id', $sourceId)->first(['payload']);
        $native = $leadRecord === null ? DB::table('crm_native_opportunities')
            ->where('source_id', $sourceId)->first(['payload']) : null;
        $leadRecord ??= $native;
        if ($leadRecord === null) {
            return null;
        }

        $lead = $this->decode($leadRecord->payload);
        $opportunity = $this->pipeline->find($sourceId);
        if ($opportunity === null) {
            return null;
        }

        $legacyProfile = $this->pick($lead, self::PROFILE_FIELDS);
        $profile = $legacyProfile;
        $currentDetail = DB::table('crm_lead_details')->where('lead_source_id', $sourceId)->first();
        $detailEditor = $currentDetail === null ? null : DB::table('crm_users')
            ->where('id', $currentDetail->updated_by_user_id)->value('name');
        if ($currentDetail !== null) {
            $profile = array_replace($profile, json_decode($currentDetail->profile, true, 512, JSON_THROW_ON_ERROR));
        }
        $profile['map_url'] = $this->safeHttpUrl($profile['map_url']);
        $site = Site::query()->with(['owner:id,name', 'contacts'])->where('legacy_lead_id', $sourceId)
            ->first(['id', 'site_code', 'name', 'area', 'location_type', 'map_url', 'source', 'owner_user_id', 'legacy_owner_label']);
        if ($site !== null) {
            $profile['site_name'] = $site->name;
            $profile['area'] = $site->area;
            $profile['location_type'] = $site->location_type;
            $profile['map_url'] = $this->safeHttpUrl($site->map_url);
            $profile['lead_source'] = $site->source ?? $profile['lead_source'];
            $profile['assigned_to'] = $site->owner?->name ?? $site->legacy_owner_label ?? $profile['assigned_to'];
            $contact = $site->contacts->firstWhere('is_primary', true) ?? $site->contacts->first();
            if ($contact !== null) {
                foreach ([
                    'contact_name' => 'name',
                    'contact_role' => 'role',
                    'phone' => 'phone',
                    'line_name' => 'line_name',
                    'email' => 'email',
                ] as $profileField => $contactField) {
                    $profile[$profileField] = $contact->{$contactField};
                }
            }
        }
        $profile['contacts'] = $site === null
            ? []
            : $site->contacts
                ->sortByDesc('is_primary')
                ->values()
                ->map(static fn ($contact): array => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'role' => $contact->role,
                    'phone' => $contact->phone,
                    'line_name' => $contact->line_name,
                    'email' => $contact->email,
                    'is_primary' => (bool) $contact->is_primary,
                ])
                ->all();
        $profile['current_site'] = $site === null ? null : [
            'id' => $site->id,
            'site_code' => $site->site_code,
            'site_name' => $site->name,
            'area' => $site->area,
            'owner' => $site->owner?->name ?? $site->legacy_owner_label,
            'owner_user_id' => $site->owner_user_id,
            'fingerprint' => LeadSiteFingerprint::for($site),
        ];

        $related = [];
        foreach (self::RELATED_FIELDS as $table => $fields) {
            $rows = DB::table('crm_legacy_records')
                ->where('batch_id', $batchId)->where('source_table', $table)
                ->get(['source_id', 'payload']);
            $related[$table] = [];
            foreach ($rows as $row) {
                $payload = $this->decode($row->payload);
                if (($payload['lead_id'] ?? null) !== $sourceId) {
                    continue;
                }
                $related[$table][] = $this->pick($payload, $fields);
            }
            $timeField = $table === 'activities' || $table === 'visit_logs' ? 'occurred_at' : 'created_at';
            usort($related[$table], static function (array $a, array $b) use ($timeField): int {
                $aTime = strtotime((string) ($a[$timeField] ?? ''));
                $bTime = strtotime((string) ($b[$timeField] ?? ''));

                return (($bTime === false ? PHP_INT_MIN : $bTime) <=> ($aTime === false ? PHP_INT_MIN : $aTime))
                    ?: strcmp((string) ($b['id'] ?? ''), (string) ($a['id'] ?? ''));
            });
        }

        $related['visit_logs'] = array_map(static fn (array $visit): array => $visit + [
            'origin' => 'legacy',
            'editable' => false,
        ], $related['visit_logs']);
        $currentVisits = CrmVisitLog::query()->where('lead_source_id', $sourceId)
            ->orderByDesc('occurred_at')->orderByDesc('id')->get();
        foreach ($currentVisits as $visit) {
            $related['visit_logs'][] = [
                'id' => $visit->id,
                'visit_type' => $visit->visit_type,
                'occurred_at' => $visit->occurred_at?->toIso8601String(),
                'internal_participants' => $visit->internal_participants,
                'customer_participants' => $visit->customer_participants,
                'location' => $visit->location,
                'summary' => $visit->summary,
                'site_findings' => $visit->site_findings,
                'decisions' => $visit->decisions,
                'risks' => $visit->risks,
                'next_steps' => $visit->next_steps,
                'next_action_at' => $visit->next_action_at?->toIso8601String(),
                'origin' => 'current',
                'editable' => true,
            ];
        }
        usort($related['visit_logs'], static fn (array $a, array $b): int => strcmp((string) ($b['occurred_at'] ?? ''), (string) ($a['occurred_at'] ?? ''))
            ?: strcmp((string) ($b['id'] ?? ''), (string) ($a['id'] ?? '')));
        $profile['_source'] = $currentDetail === null ? ($native === null ? 'legacy' : 'native') : 'current';
        $profile['_legacy'] = $legacyProfile;
        $profile['_version'] = $currentDetail?->version;
        $profile['_updated_by'] = $detailEditor;
        $profile['_updated_at'] = $currentDetail?->updated_at;

        $related['attachments'] = array_map(static fn (array $file): array => $file + [
            'origin' => 'legacy',
            'content_url' => null,
        ], $related['attachments']);
        foreach (CrmAttachment::query()->with('uploader:id,name')->where('lead_source_id', $sourceId)->whereNull('deleted_at')->orderByDesc('created_at')->orderByDesc('id')->get() as $file) {
            $related['attachments'][] = [
                'id' => $file->id,
                'visit_log_id' => $file->visit_log_id,
                'category' => $file->category,
                'file_name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'size_bytes' => $file->size_bytes,
                'note' => $file->note,
                'created_at' => $file->created_at?->toIso8601String(),
                'uploaded_by' => $file->uploader?->name,
                'can_replace' => auth('crm')->user()?->hasPermission('pipeline.log')
                    && ($file->uploaded_by_user_id === auth('crm')->id() || auth('crm')->user()?->hasPermission('system.manage')),
                'origin' => 'current',
                'content_url' => route('crm.admin.pipeline.attachments.show', [$sourceId, $file->id]),
            ];
        }
        usort($related['attachments'], static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''))
            ?: strcmp((string) ($b['id'] ?? ''), (string) ($a['id'] ?? '')));

        $technical = $this->pick($lead, self::TECHNICAL_FIELDS);
        if ($currentDetail !== null) {
            $technical = array_replace($technical, json_decode($currentDetail->technical, true, 512, JSON_THROW_ON_ERROR));
        }
        $technical['_source'] = $currentDetail === null ? ($native === null ? 'legacy' : 'native') : 'current';
        $technical['_updated_by'] = $detailEditor;
        $technical['_updated_at'] = $currentDetail?->updated_at;
        $currentProject = DB::table('crm_engineering_projects')->where('lead_source_id', $sourceId)->first();
        $technical['project'] = $currentProject === null
            ? (isset($related['projects'][0]) ? $related['projects'][0] + ['origin' => 'legacy'] : null)
            : [
                'id' => $currentProject->id,
                'project_code' => $currentProject->project_code,
                'engineering_stage' => $currentProject->engineering_stage,
                'sync_status' => $currentProject->sync_status,
                'origin' => $currentProject->origin,
                'legacy_project_id' => $currentProject->legacy_project_id,
            ];
        $legacyBusiness = $this->pick($lead, self::BUSINESS_FIELDS);
        $currentBusiness = CrmBusinessTerms::query()->with('editor:id,name')->where('lead_source_id', $sourceId)->first();
        $business = $currentBusiness === null ? $legacyBusiness : array_replace($legacyBusiness, $currentBusiness->values);
        $business['_source'] = $currentBusiness === null ? ($native === null ? 'legacy' : 'native') : 'current';
        $business['_legacy'] = $legacyBusiness;
        $business['_version'] = $currentBusiness?->version;
        $business['_updated_by'] = $currentBusiness?->editor?->name;
        $business['_updated_at'] = $currentBusiness?->updated_at?->toIso8601String();

        return [
            'opportunity' => $opportunity,
            'sections' => [
                'profile' => $profile,
                'business' => $business,
                'technical' => $technical,
                'activities' => $related['activities'],
                'visits' => $related['visit_logs'],
                'attachments' => $related['attachments'],
            ],
        ];
    }

    private function decode(string $json): array
    {
        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload) || array_is_list($payload)) {
            throw new JsonException('Imported record is not an object.');
        }

        return $payload;
    }

    private function pick(array $source, array $fields): array
    {
        return array_replace(array_fill_keys($fields, null), array_intersect_key($source, array_flip($fields)));
    }

    private function safeHttpUrl(mixed $value): ?string
    {
        if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        if (parse_url($value, PHP_URL_USER) !== null || parse_url($value, PHP_URL_PASS) !== null) {
            return null;
        }

        return in_array($scheme, ['http', 'https'], true) ? $value : null;
    }
}
