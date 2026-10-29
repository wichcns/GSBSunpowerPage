<?php

namespace App\Crm\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LineOaGroupChannel
{
    private const PUSH_URL = 'https://api.line.me/v2/bot/message/push';

    private const MAX_ATTEMPTS = 3;

    private const STALE_PROCESSING_MINUTES = 10;

    public function sendTest(): bool
    {
        if ($this->destination() === null) {
            return false;
        }

        return $this->send('TEST', 'test:'.Str::uuid(), "ทดสอบการแจ้งเตือนจาก GSB EV CRM\nระบบเชื่อมต่อกลุ่ม LINE ส่วนกลางสำเร็จ");
    }

    public function queue(string $type, string $eventKey, string $message): bool
    {
        return DB::table('crm_notification_outbox')->insertOrIgnore([
            'event_key' => $eventKey, 'channel' => 'line_group', 'recipient' => null,
            'notification_type' => $type, 'payload' => json_encode(['text' => $message], JSON_THROW_ON_ERROR),
            'status' => 'pending', 'attempts' => 0, 'retry_key' => (string) Str::uuid(),
            'next_attempt_at' => null, 'processing_at' => null,
            'created_at' => now(), 'updated_at' => now(),
        ]) === 1;
    }

    public function dispatchPending(int $limit = 20): int
    {
        $this->recoverStaleProcessing();
        $destination = $this->destination();
        if ($destination === null) {
            return 0;
        }
        [$group, $token] = $destination;
        $sent = 0;
        $items = DB::table('crm_notification_outbox')
            ->where(function ($query): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($retry): void {
                        $retry->where('status', 'failed')
                            ->where('attempts', '<', self::MAX_ATTEMPTS)
                            ->where('next_attempt_at', '<=', now());
                    });
            })
            ->orderBy('id')->limit($limit)->get();

        foreach ($items as $item) {
            $claimed = $this->claim((int) $item->id, (string) $item->status, (int) $item->attempts);
            if ($claimed !== null && $this->deliver($claimed, $group->group_id, $token)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function send(string $type, string $eventKey, string $message): bool
    {
        if (! $this->queue($type, $eventKey, $message)) {
            return false;
        }
        $destination = $this->destination();
        if ($destination === null) {
            return false;
        }
        $item = DB::table('crm_notification_outbox')->where('event_key', $eventKey)->first();
        if ($item === null) {
            return false;
        }
        $claimed = $this->claim((int) $item->id, (string) $item->status, (int) $item->attempts);

        return $claimed !== null && $this->deliver($claimed, $destination[0]->group_id, $destination[1]);
    }

    public function retry(int $id): bool
    {
        $destination = $this->destination();
        if ($destination === null) {
            return false;
        }

        $reset = DB::table('crm_notification_outbox')->where('id', $id)
            ->whereIn('status', ['failed', 'exhausted'])
            ->update([
                'status' => 'pending', 'attempts' => 0, 'next_attempt_at' => null,
                'processing_at' => null, 'last_error' => null, 'sent_at' => null, 'updated_at' => now(),
            ]);
        if ($reset !== 1) {
            return false;
        }

        $item = DB::table('crm_notification_outbox')->where('id', $id)->first();
        $claimed = $item === null ? null : $this->claim($id, 'pending', 0);

        return $claimed !== null && $this->deliver($claimed, $destination[0]->group_id, $destination[1]);
    }

    private function destination(): ?array
    {
        if (! (bool) config('services.line_oa.sending_enabled')) {
            return null;
        }
        $group = DB::table('crm_line_groups')->where('is_confirmed', true)->where('is_enabled', true)->first();
        $token = (string) config('services.line_oa.channel_token');

        return $group === null || $token === '' ? null : [$group, $token];
    }

    private function deliver(object $item, string $groupId, string $token): bool
    {
        try {
            $payload = json_decode($item->payload, true, 512, JSON_THROW_ON_ERROR);
            $response = Http::withToken($token)->withHeaders(['X-Line-Retry-Key' => $item->retry_key])
                ->acceptJson()->connectTimeout(3)->timeout(5)->post(self::PUSH_URL, [
                    'to' => $groupId,
                    'messages' => [['type' => 'text', 'text' => (string) ($payload['text'] ?? '')]],
                ]);
            if (! $response->successful()) {
                throw new \RuntimeException('LINE API '.$response->status().': '.$response->body());
            }
            DB::table('crm_notification_outbox')->where('id', $item->id)->update([
                'recipient' => $groupId, 'status' => 'sent', 'attempts' => $item->attempts + 1,
                'sent_at' => now(), 'last_error' => null, 'next_attempt_at' => null,
                'processing_at' => null, 'updated_at' => now(),
            ]);

            return true;
        } catch (\Throwable $error) {
            $attempts = $item->attempts + 1;
            DB::table('crm_notification_outbox')->where('id', $item->id)->update([
                'recipient' => $groupId, 'status' => $attempts >= self::MAX_ATTEMPTS ? 'exhausted' : 'failed',
                'attempts' => $attempts,
                'next_attempt_at' => $attempts >= self::MAX_ATTEMPTS ? null : now()->addMinutes($attempts === 1 ? 5 : 15),
                'processing_at' => null,
                'last_error' => Str::limit($error->getMessage(), 2000), 'updated_at' => now(),
            ]);

            return false;
        }
    }

    private function claim(int $id, string $status, int $attempts): ?object
    {
        $claimed = DB::table('crm_notification_outbox')
            ->where('id', $id)->where('status', $status)->where('attempts', $attempts)
            ->update(['status' => 'processing', 'processing_at' => now(), 'updated_at' => now()]);

        return $claimed === 1 ? DB::table('crm_notification_outbox')->where('id', $id)->first() : null;
    }

    private function recoverStaleProcessing(): void
    {
        DB::table('crm_notification_outbox')
            ->where('status', 'processing')
            ->where('processing_at', '<=', now()->subMinutes(self::STALE_PROCESSING_MINUTES))
            ->update([
                'status' => 'failed', 'next_attempt_at' => now(), 'processing_at' => null,
                'last_error' => 'การส่งค้างเกิน 10 นาที ระบบนำกลับเข้าคิวอัตโนมัติ', 'updated_at' => now(),
            ]);
    }
}
