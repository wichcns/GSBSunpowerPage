<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Actions\ChangeSalesStage;
use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\Services\LineOaGroupChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    public function update(string $sourceId, Request $request, ChangeSalesStage $change, LegacyPipeline $pipeline, LineOaGroupChannel $line): JsonResponse
    {
        $input = $request->validate([
            'stage' => ['required', 'string', 'in:NEW,NEGOTIATING,REVIEW,MOU,CONTRACT,SIGNED,ON_HOLD,CANCELLED'],
            'expected_version' => ['required', 'string', 'max:160'],
        ]);
        $result = $change->execute($sourceId, $input['stage'], $input['expected_version'], (int) $request->user()->id);
        $line->dispatchPending();

        return response()->json(['opportunity' => $pipeline->find($sourceId), 'project' => $result['project']]);
    }
}
