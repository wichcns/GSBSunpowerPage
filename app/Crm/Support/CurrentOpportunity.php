<?php

namespace App\Crm\Support;

use Illuminate\Support\Facades\DB;

class CurrentOpportunity
{
    public static function record(string $sourceId, bool $lock = false): ?object
    {
        if (DB::table('crm_archived_opportunities')->where('source_id', $sourceId)->exists()) {
            return null;
        }
        $batchId = DB::table('crm_legacy_import_batches')->where('status', 'completed')->latest('id')->value('id');
        if ($batchId !== null) {
            $query = DB::table('crm_legacy_records')->where('batch_id', $batchId)
                ->where('source_table', 'leads')->where('source_id', $sourceId);
            $record = ($lock ? $query->lockForUpdate() : $query)->first(['payload']);
            if ($record !== null) {
                return $record;
            }
        }

        $query = DB::table('crm_native_opportunities')->where('source_id', $sourceId);

        return ($lock ? $query->lockForUpdate() : $query)->first(['payload']);
    }
}
