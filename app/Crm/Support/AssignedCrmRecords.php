<?php

namespace App\Crm\Support;

use App\Crm\Models\User;
use Illuminate\Support\Facades\DB;

class AssignedCrmRecords
{
    public function pipeline(User $user): array
    {
        return DB::table('crm_sites')->where('owner_user_id', $user->id)
            ->whereNotNull('legacy_lead_id')->pluck('legacy_lead_id')->all();
    }

    public function engineering(User $user, ?string $kind = null): array
    {
        $survey = $kind === 'construction' ? [] : DB::table('crm_engineering_survey_tasks')
            ->where('assignee_user_id', $user->id)->where('status', 'scheduled')->pluck('lead_source_id')->all();
        $construction = $kind === 'survey' ? [] : DB::table('crm_engineering_construction_records')
            ->where('owner_user_id', $user->id)->pluck('lead_source_id')->all();

        return array_values(array_unique([...$survey, ...$construction]));
    }
}
