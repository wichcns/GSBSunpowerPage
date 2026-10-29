<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\CrmVisitLog;
use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Support\Facades\DB;

class ActivityCenter
{
    public const TYPES = [
        'NEXT_ACTION' => 'งานถัดไป',
        'FOLLOW_UP' => 'ติดตาม',
        'MEETING' => 'ประชุม',
        'SITE_VISIT' => 'เข้าพบหน้างาน',
        'SITE_SURVEY' => 'สำรวจหน้างาน',
        'NEGOTIATION' => 'เจรจา',
        'SYSTEM_ACTIVITY' => 'กิจกรรมระบบ',
    ];

    public function __construct(private readonly LegacyPipeline $pipeline) {}

    /** @param array<string, mixed> $filters */
    public function forUser(User $user, array $filters = []): array
    {
        $leads = $this->pipeline->all();
        $allowed = $this->allowedSourceIds($user, $leads);
        $sites = Site::query()->with('owner:id,name')->whereIn('legacy_lead_id', $allowed)
            ->get()->keyBy('legacy_lead_id');
        $items = [];

        foreach ($allowed as $sourceId) {
            $lead = $leads[$sourceId] ?? null;
            if ($lead === null || $lead['stage_code'] === 'CANCELLED' || $lead['next_action_raw'] === '') {
                continue;
            }
            $site = $sites->get($sourceId);
            $items[] = $this->normalizeItem(
                key: 'task/'.$sourceId,
                sourceId: $sourceId,
                siteName: $lead['site_name'],
                type: 'NEXT_ACTION',
                title: $lead['next_action_raw'],
                occurredAt: $lead['next_action_at_raw'],
                origin: $lead['stage_origin'] === 'current' ? 'current' : 'legacy',
                ownerId: $site?->owner_user_id,
                ownerName: $site?->owner?->name ?? $lead['owner'],
                detail: ['next_steps' => $lead['next_action_raw'], 'next_action_at' => $lead['next_action_at_raw']],
                isTask: true,
            );
        }

        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        if ($batchId !== null && $allowed !== []) {
            foreach (DB::table('crm_legacy_records')->where('batch_id', $batchId)
                ->whereIn('source_table', ['activities', 'visit_logs'])->get(['source_table', 'source_id', 'payload']) as $record) {
                $payload = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
                $sourceId = (string) ($payload['lead_id'] ?? '');
                if (! in_array($sourceId, $allowed, true) || ! isset($leads[$sourceId])) {
                    continue;
                }
                $site = $sites->get($sourceId);
                $isVisit = $record->source_table === 'visit_logs';
                $items[] = $this->normalizeItem(
                    key: 'legacy/'.$record->source_table.'/'.$record->source_id,
                    sourceId: $sourceId,
                    siteName: $leads[$sourceId]['site_name'],
                    type: $isVisit ? (string) ($payload['visit_type'] ?? 'FOLLOW_UP') : 'SYSTEM_ACTIVITY',
                    title: (string) ($payload['summary'] ?? __('activities.legacy_activity')),
                    occurredAt: (string) ($payload['occurred_at'] ?? ''),
                    origin: 'legacy',
                    ownerId: $site?->owner_user_id,
                    ownerName: $site?->owner?->name ?? $leads[$sourceId]['owner'],
                    detail: $payload,
                );
            }
        }

        foreach (CrmVisitLog::query()->with('creator:id,name')->whereIn('lead_source_id', $allowed)->get() as $visit) {
            $sourceId = $visit->lead_source_id;
            if (! isset($leads[$sourceId])) {
                continue;
            }
            $site = $sites->get($sourceId);
            $items[] = $this->normalizeItem(
                key: 'current/visit/'.$visit->id,
                sourceId: $sourceId,
                siteName: $leads[$sourceId]['site_name'],
                type: $visit->visit_type,
                title: $visit->summary,
                occurredAt: $visit->occurred_at?->toIso8601String() ?? '',
                origin: 'current',
                ownerId: $site?->owner_user_id,
                ownerName: $site?->owner?->name ?? $leads[$sourceId]['owner'],
                detail: [
                    'internal_participants' => $visit->internal_participants,
                    'customer_participants' => $visit->customer_participants,
                    'location' => $visit->location,
                    'summary' => $visit->summary,
                    'next_steps' => $visit->next_steps,
                    'next_action_at' => $visit->next_action_at?->toIso8601String(),
                    'created_by' => $visit->creator?->name,
                ],
            );
        }

        $summary = [
            'total' => count($items),
            'today' => count(array_filter($items, fn (array $item) => $item['due_status'] === 'today')),
            'overdue' => count(array_filter($items, fn (array $item) => $item['due_status'] === 'overdue')),
            'undated' => count(array_filter($items, fn (array $item) => $item['due_status'] === 'undated')),
        ];
        $filtered = array_values(array_filter($items, fn (array $item): bool => $this->matches($item, $filters)));
        usort($filtered, static fn (array $a, array $b): int => $b['sort_at'] <=> $a['sort_at'] ?: strcmp($b['key'], $a['key']));

        return [
            'items' => $filtered,
            'summary' => $summary,
            'owners' => $sites->pluck('owner')->filter()->unique('id')->sortBy('name')->values(),
            'types' => collect(array_keys(self::TYPES))->mapWithKeys(fn (string $code): array => [$code => $this->typeLabel($code)])->all(),
        ];
    }

    /** @param array<string, array> $leads */
    private function allowedSourceIds(User $user, array $leads): array
    {
        if (in_array($user->role?->slug, ['super_admin', 'admin'], true)) {
            return array_keys($leads);
        }

        $query = Site::query()->whereNotNull('legacy_lead_id');
        if ($user->role?->slug === 'manager') {
            $query->whereHas('owner.role', fn ($role) => $role->where('slug', 'staff'));
        } else {
            $query->where('owner_user_id', $user->id);
        }

        return $query->pluck('legacy_lead_id')->filter(fn ($id) => isset($leads[$id]))->values()->all();
    }

    /** @param array<string, mixed> $detail */
    private function normalizeItem(string $key, string $sourceId, string $siteName, string $type, string $title,
        string $occurredAt, string $origin, ?int $ownerId, ?string $ownerName, array $detail, bool $isTask = false): array
    {
        $timestamp = $occurredAt !== '' ? strtotime($occurredAt) : false;
        $dueStatus = null;
        if ($isTask) {
            $dueStatus = $timestamp === false ? 'undated'
                : ($timestamp < now()->startOfDay()->timestamp ? 'overdue'
                    : ($timestamp <= now()->endOfDay()->timestamp ? 'today' : 'upcoming'));
        }

        return [
            'key' => $key,
            'source_id' => $sourceId,
            'site_name' => $siteName,
            'type' => array_key_exists($type, self::TYPES) ? $type : 'SYSTEM_ACTIVITY',
            'type_label' => $this->typeLabel($type),
            'title' => $title !== '' ? $title : __('activities.no_details'),
            'occurred_at' => $timestamp === false ? null : $occurredAt,
            'occurred_label' => $timestamp === false ? __('activities.no_date') : date('d/m/Y H:i', $timestamp),
            'sort_at' => $timestamp === false ? PHP_INT_MIN : $timestamp,
            'origin' => $origin,
            'origin_label' => $origin === 'current' ? __('activities.current_origin') : __('activities.legacy_origin'),
            'owner_user_id' => $ownerId,
            'owner_name' => $ownerName ?: __('activities.unassigned'),
            'due_status' => $dueStatus,
            'detail' => $detail,
            'pipeline_url' => route('crm.admin.pipeline.index', ['open' => $sourceId, 'tab' => 'activity']),
            'visit_url' => route('crm.admin.pipeline.visits.store', $sourceId),
        ];
    }

    private function typeLabel(string $type): string
    {
        $key = match ($type) {
            'NEXT_ACTION' => 'next_action',
            'FOLLOW_UP' => 'follow_up',
            'MEETING' => 'meeting',
            'SITE_VISIT' => 'site_visit',
            'SITE_SURVEY' => 'survey',
            'NEGOTIATION' => 'negotiation',
            default => 'system_activity',
        };

        return __('activities.'.$key);
    }

    /** @param array<string, mixed> $filters */
    private function matches(array $item, array $filters): bool
    {
        $q = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        if ($q !== '' && ! str_contains(mb_strtolower($item['site_name'].' '.$item['title'].' '.$item['owner_name']), $q)) {
            return false;
        }
        if (! empty($filters['type']) && $item['type'] !== $filters['type']) {
            return false;
        }
        if (! empty($filters['owner']) && $item['owner_user_id'] !== (int) $filters['owner']) {
            return false;
        }
        if (! empty($filters['due']) && $item['due_status'] !== $filters['due']) {
            return false;
        }
        if (! empty($filters['date_from']) && ($item['sort_at'] === PHP_INT_MIN || $item['sort_at'] < strtotime($filters['date_from'].' 00:00:00'))) {
            return false;
        }
        if (! empty($filters['date_to']) && ($item['sort_at'] === PHP_INT_MIN || $item['sort_at'] > strtotime($filters['date_to'].' 23:59:59'))) {
            return false;
        }

        return true;
    }
}
