<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Actions\CreateSiteOpportunity;
use App\Crm\Actions\SaveSite;
use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\SaveSiteRequest;
use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\ReadModels\SiteDirectory;
use App\Crm\ReadModels\SiteContactPreview;
use App\Crm\Services\LineOaGroupChannel;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function index(Request $request, SiteDirectory $directory)
    {
        return view('crm.admin.sites.index', $directory->forRequest($request));
    }

    public function show(Site $site, SiteContactPreview $contactPreview, LegacyPipeline $pipeline)
    {
        $site->load(['contacts', 'owner:id,name', 'audits.actor:id,name']);
        $opportunity = $site->legacy_lead_id ? $pipeline->find($site->legacy_lead_id) : null;

        return response()->json([
            'site' => $site->toArray() + [
                'edit_fingerprint' => SiteEditFingerprint::for($site),
                'contact_preview' => $contactPreview->forSites(collect([$site]))[$site->id] ?? null,
                'pipeline_url' => $site->legacy_lead_id
                    ? route('crm.admin.pipeline.index', ['open' => $site->legacy_lead_id])
                    : null,
                'opportunity_create_url' => $site->legacy_lead_id === null
                    ? route('crm.admin.sites.opportunities.store', $site)
                    : null,
                'stage_code' => $opportunity['stage_code'] ?? null,
                'stage_label' => $opportunity['stage_label'] ?? null,
            ]
        ]);
    }

    // public function store(SaveSiteRequest $request, SaveSite $saveSite, LineOaGroupChannel $line)
    // {
    //     $site = $saveSite->create($request->validated(), $request->user());
    //     $line->dispatchPending();

    //     return redirect()->route('crm.admin.pipeline.index', ['section' => 'sites', 'site' => $site->id])->with('status', 'Site created.');
    // }


    public function store(
        SaveSiteRequest $request,
        SaveSite $saveSite,
        CreateSiteOpportunity $createOpportunity,
        LineOaGroupChannel $line
    ) {
        $sourceId = DB::transaction(function () use ($request, $saveSite, $createOpportunity) {
            $site = $saveSite->create(
                $request->validated(),
                $request->user()
            );

            return $createOpportunity->execute(
                $site,
                $request->user()
            );
        });

        $line->dispatchPending();

        return redirect()
            ->route('crm.admin.pipeline.index', [
                'open' => $sourceId,
            ])
            ->with('status', 'Site and opportunity created successfully.');
    }


    public function createOpportunity(Site $site, CreateSiteOpportunity $createOpportunity)
    {
        $sourceId = $createOpportunity->execute($site, request()->user());

        return redirect()->route('crm.admin.pipeline.index', ['open' => $sourceId]);
    }

    public function update(SaveSiteRequest $request, Site $site, SaveSite $saveSite, LineOaGroupChannel $line)
    {
        $saveSite->update($site, $request->validated(), $request->user());
        $line->dispatchPending();

        return redirect()->route('crm.admin.pipeline.index', ['section' => 'sites', 'site' => $site->id])->with('status', 'Site updated.');
    }
}
