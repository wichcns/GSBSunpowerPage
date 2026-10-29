<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\ReadModels\MyWork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyWorkController extends Controller
{
    public function index(Request $request, MyWork $work): View
    {
        return view('crm.admin.my-work', $this->payload($request, $work));
    }

    public function items(Request $request, MyWork $work): JsonResponse
    {
        return response()->json($this->payload($request, $work));
    }

    private function payload(Request $request, MyWork $work): array
    {
        $items = $work->forUser($request->user()->id);

        return [
            'items' => $items,
            'sites' => $work->sitesForUser($request->user()->id),
            'summary' => [
                'total' => count($items),
                'overdue' => count(array_filter($items, fn (array $item): bool => $item['overdue'])),
                'undated' => count(array_filter($items, fn (array $item): bool => $item['due_at'] === null)),
            ],
        ];
    }
}
