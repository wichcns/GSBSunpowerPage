<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Actions\ChangeEngineeringStage;
use App\Crm\Actions\ManageEngineeringConstruction;
use App\Crm\Actions\SaveEngineeringSurvey;
use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\AddEngineeringConstructionUpdateRequest;
use App\Crm\Http\Requests\CompleteEngineeringSurveyRequest;
use App\Crm\Http\Requests\SaveEngineeringConstructionRequest;
use App\Crm\Http\Requests\SaveEngineeringSurveyRequest;
use App\Crm\Http\Requests\StartEngineeringConstructionRequest;
use App\Crm\Http\Requests\UpdateEngineeringStageRequest;
use App\Crm\ReadModels\EngineeringConstruction;
use App\Crm\ReadModels\EngineeringProjects;
use App\Crm\ReadModels\EngineeringSurveyQueue;
use App\Crm\Services\LineOaGroupChannel;
use App\Crm\Support\AssignedCrmRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EngineeringController extends Controller
{
    public function index(Request $request, EngineeringProjects $projects, EngineeringSurveyQueue $surveys, AssignedCrmRecords $assigned): View
    {
        $rows = $projects->all();
        $surveyQueue = $surveys->all();
        if (! $request->user()->hasPermission('engineering.manage')) {
            $allowed = array_flip($assigned->engineering($request->user()));
            $rows = array_values(array_filter($rows, fn (array $row) => isset($allowed[$row['lead_source_id']])));
            $surveyQueue = array_values(array_filter($surveyQueue, fn (array $row) => isset($allowed[$row['lead_source_id']])));
        }

        return view('crm.admin.engineering-index', ['projects' => $rows,
            'initialStages' => array_column($rows, 'engineering_stage', 'lead_source_id'),
            'surveyQueue' => $surveyQueue,
            'surveyUsers' => $request->user()->hasPermission('engineering.manage')
                ? DB::table('crm_users')->orderBy('name')->get(['id', 'name']) : collect()]);
    }

    public function show(string $sourceId, EngineeringProjects $projects, EngineeringSurveyQueue $surveys, EngineeringConstruction $construction): JsonResponse
    {
        $project = $projects->find($sourceId);
        abort_if($project === null, 404);

        return response()->json(['project' => $project, 'survey_task' => $surveys->forProject($sourceId),
            'construction' => $construction->forProject($sourceId),
            'stage_history' => $projects->historyFor($sourceId)]);
    }

    public function updateStage(string $sourceId, UpdateEngineeringStageRequest $request, ChangeEngineeringStage $action, EngineeringSurveyQueue $surveys, EngineeringConstruction $construction, EngineeringProjects $projects, LineOaGroupChannel $line): JsonResponse
    {
        $data = $request->validated();

        $result = $action->execute($sourceId, $data['engineering_stage'], $data['expected_version'], $request->user()->id);
        $line->dispatchPending();

        return response()->json([...$result, 'survey_task' => $surveys->forProject($sourceId),
            'construction' => $construction->forProject($sourceId),
            'stage_history' => $projects->historyFor($sourceId)]);
    }

    public function saveSurvey(string $sourceId, SaveEngineeringSurveyRequest $request, SaveEngineeringSurvey $action, EngineeringConstruction $construction): JsonResponse
    {
        $data = $request->validated();

        $result = $action->schedule($sourceId, $data['assignee_user_id'] ?? null, $data['scheduled_at'] ?? null, $data['expected_version'], $request->user()->id);

        return response()->json([...$result, 'construction' => $construction->forProject($sourceId)]);
    }

    public function completeSurvey(string $sourceId, CompleteEngineeringSurveyRequest $request, SaveEngineeringSurvey $action, EngineeringConstruction $construction, EngineeringProjects $projects, LineOaGroupChannel $line): JsonResponse
    {
        $data = $request->validated();

        $result = $action->complete($sourceId, $data['result_summary'], $data['expected_version'], $data['expected_project_version'], $request->user()->id);
        $line->dispatchPending();

        return response()->json([...$result, 'construction' => $construction->forProject($sourceId),
            'stage_history' => $projects->historyFor($sourceId)]);
    }

    public function startConstruction(string $sourceId, StartEngineeringConstructionRequest $request, ManageEngineeringConstruction $action, EngineeringProjects $projects, LineOaGroupChannel $line): JsonResponse
    {
        $result = $action->start($sourceId, $request->validated(), $request->user()->id);
        $line->dispatchPending();

        return response()->json([...$result, 'stage_history' => $projects->historyFor($sourceId)]);
    }

    public function saveConstruction(string $sourceId, SaveEngineeringConstructionRequest $request, ManageEngineeringConstruction $action): JsonResponse
    {
        return response()->json($action->saveDetails($sourceId, $request->validated(), $request->user()->id));
    }

    public function addConstructionUpdate(string $sourceId, AddEngineeringConstructionUpdateRequest $request, ManageEngineeringConstruction $action, LineOaGroupChannel $line): JsonResponse
    {
        $data = $request->validated();

        $result = $action->addUpdate($sourceId, $data['note'], $data['expected_version'], $request->user()->id);
        $line->dispatchPending();

        return response()->json($result);
    }
}
