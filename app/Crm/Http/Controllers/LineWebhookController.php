<?php

namespace App\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LineWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = (string) config('services.line_oa.channel_secret');
        $signature = (string) $request->header('X-Line-Signature');
        $expected = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));
        abort_if($secret === '' || $signature === '' || ! hash_equals($expected, $signature), 403);

        foreach ((array) $request->input('events', []) as $event) {
            $source = $event['source'] ?? [];
            if (($event['type'] ?? '') !== 'join' || ($source['type'] ?? '') !== 'group' || empty($source['groupId'])) {
                continue;
            }
            DB::table('crm_line_groups')->updateOrInsert(['group_id' => $source['groupId']], [
                'last_seen_at' => now(), 'updated_at' => now(), 'created_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
