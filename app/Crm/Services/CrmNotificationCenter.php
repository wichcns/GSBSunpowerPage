<?php

namespace App\Crm\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CrmNotificationCenter
{
    public function notify(iterable $userIds, string $eventKey, string $type, string $title, ?string $body, ?string $actionUrl, array $translations = []): int
    {
        $actionUrl = is_string($actionUrl) && str_starts_with($actionUrl, '/crm/admin/')
            ? $actionUrl : route('crm.admin.notifications.index');
        $created = 0;
        foreach (array_values(array_unique(array_filter(array_map('intval', [...$userIds])))) as $userId) {
            $created += DB::table('crm_notifications')->insertOrIgnore([
                'user_id' => $userId, 'event_key' => $eventKey, 'type' => $type,
                'title' => $title, 'body' => $body,
                'translations' => $translations === [] ? null : json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'action_url' => $actionUrl,
                'read_at' => null, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $created;
    }

    public function notifyForLead(string $sourceId, string $eventKey, string $type, string $title, ?string $body, ?string $actionUrl): int
    {
        return $this->notify($this->recipientIdsForLead($sourceId), $eventKey, $type, $title, $body, $actionUrl);
    }

    public function recipientIdsForLead(string $sourceId): array
    {
        return DB::table('crm_sites')->where('legacy_lead_id', $sourceId)
            ->whereNotNull('owner_user_id')->pluck('owner_user_id')->map(fn ($id) => (int) $id)->all();
    }

    public function recipientIdsForEngineering(string $sourceId): array
    {
        return array_values(array_unique(array_filter([
            ...$this->recipientIdsForLead($sourceId),
            (int) DB::table('crm_engineering_survey_tasks')->where('lead_source_id', $sourceId)->value('assignee_user_id'),
            (int) DB::table('crm_engineering_construction_records')->where('lead_source_id', $sourceId)->value('owner_user_id'),
        ])));
    }

    public function latest(int $userId, int $limit = 5): Collection
    {
        return DB::table('crm_notifications')->where('user_id', $userId)->latest('id')->limit($limit)->get();
    }

    public function history(int $userId, int $perPage = 30): LengthAwarePaginator
    {
        return DB::table('crm_notifications')->where('user_id', $userId)->latest('id')->paginate($perPage);
    }

    public function unreadCount(int $userId): int
    {
        return DB::table('crm_notifications')->where('user_id', $userId)->whereNull('read_at')->count();
    }

    public function open(int $userId, int $notificationId): ?string
    {
        $item = DB::table('crm_notifications')->where('id', $notificationId)->where('user_id', $userId)->first();
        if ($item === null) {
            return null;
        }
        if ($item->read_at === null) {
            DB::table('crm_notifications')->where('id', $item->id)->update(['read_at' => now(), 'updated_at' => now()]);
        }

        return $item->action_url ?: route('crm.admin.notifications.index');
    }

    public function markAllRead(int $userId): int
    {
        return DB::table('crm_notifications')->where('user_id', $userId)->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }
}
