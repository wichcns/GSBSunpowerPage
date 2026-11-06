<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Actions\CreateSiteOpportunity;
use App\Crm\Actions\SaveSite;
use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\SaveSiteRequest;
use App\Crm\Models\Site;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\ReadModels\SiteContactPreview;
use App\Crm\Services\LineOaGroupChannel;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function index()
    {
        return redirect()->route('crm.admin.pipeline.index', request()->filled('site') ? ['site' => request()->query('site')] : []);
    }

    public function show(Site $site, SiteContactPreview $contactPreview, LegacyPipeline $pipeline)
    {
        $this->authorizeSite($site);
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
                'stage_label' => $opportunity['display_stage_label'] ?? null,
            ],
        ]);
    }

    public function store(
        SaveSiteRequest $request,
        SaveSite $saveSite,
        CreateSiteOpportunity $createOpportunity,
        LineOaGroupChannel $line
    ) {
        abort_unless($request->user()->hasPermission('sites.write') || $request->user()->role?->slug === 'staff', 403);
        $data = $request->validated();
        if ($request->user()->role?->slug === 'staff') {
            $data['owner_user_id'] = $request->user()->id;
        }
        $sourceId = DB::transaction(function () use ($request, $saveSite, $createOpportunity, $data) {
            $site = $saveSite->create(
                $data,
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
        $this->authorizeSite($site, true);
        $data = $request->validated();
        if ($request->user()->role?->slug === 'staff') {
            $data['owner_user_id'] = $request->user()->id;
        }
        $saveSite->update($site, $data, $request->user());
        $line->dispatchPending();

        return redirect()->route('crm.admin.pipeline.index', ['site' => $site->id])->with('status', 'Site updated.');
    }

    private function authorizeSite(Site $site, bool $write = false): void
    {
        $user = request()->user();
        abort_unless($user->hasPermission($write ? 'sites.write' : 'sites.view') || $user->role?->slug === 'staff', 403);
        if ($user->role?->slug === 'staff') {
            abort_unless($site->owner_user_id === $user->id, 403);
        }
    }
}
