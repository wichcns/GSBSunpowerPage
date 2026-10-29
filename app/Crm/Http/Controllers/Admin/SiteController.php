<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Actions\SaveSite;
use App\Crm\Http\Controllers\Controller;
use App\Crm\Http\Requests\SaveSiteRequest;
use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\ReadModels\SiteContactPreview;
use App\Crm\Services\LineOaGroupChannel;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request, SiteContactPreview $contactPreview)
    {
        $sites = Site::query()->with(['owner', 'contacts'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)->orWhere('site_code', 'like', $term)
                        ->orWhere('area', 'like', $term)
                        ->orWhereHas('contacts', fn ($query) => $query->where('name', 'like', $term)->orWhere('phone', 'like', $term));
                });
            })
            ->when($request->filled('area'), fn ($query) => $query->where('area', $request->input('area')))
            ->when($request->filled('location_type'), fn ($query) => $query->where('location_type', $request->input('location_type')))
            ->when($request->filled('owner_user_id'), fn ($query) => $query->where('owner_user_id', $request->input('owner_user_id')))
            ->when($request->input('completeness') === 'complete', fn ($query) => $query
                ->whereNotNull('area')->whereNotNull('location_type')
                ->whereHas('contacts', fn ($contacts) => $contacts->where(fn ($contact) => $contact
                    ->whereNotNull('phone')->orWhereNotNull('email')->orWhereNotNull('line_name'))))
            ->when($request->input('completeness') === 'incomplete', fn ($query) => $query
                ->where(fn ($query) => $query->whereNull('area')->orWhereNull('location_type')
                    ->orWhereDoesntHave('contacts', fn ($contacts) => $contacts->where(fn ($contact) => $contact
                        ->whereNotNull('phone')->orWhereNotNull('email')->orWhereNotNull('line_name')))))
            ->latest()->paginate(20)->withQueryString();

        return view('crm.admin.sites.index', [
            'sites' => $sites,
            'areas' => Site::whereNotNull('area')->distinct()->orderBy('area')->pluck('area'),
            'types' => Site::whereNotNull('location_type')->distinct()->orderBy('location_type')->pluck('location_type'),
            'owners' => User::orderBy('name')->get(['id', 'name']),
            'contactPreviews' => $contactPreview->forSites($sites->getCollection()),
        ]);
    }

    public function show(Site $site, SiteContactPreview $contactPreview, LegacyPipeline $pipeline)
    {
        $site->load(['contacts', 'owner:id,name', 'audits.actor:id,name']);
        $opportunity = $site->legacy_lead_id ? $pipeline->find($site->legacy_lead_id) : null;

        return response()->json(['site' => $site->toArray() + [
            'edit_fingerprint' => SiteEditFingerprint::for($site),
            'contact_preview' => $contactPreview->forSites(collect([$site]))[$site->id] ?? null,
            'pipeline_url' => $site->legacy_lead_id
                ? route('crm.admin.pipeline.index', ['open' => $site->legacy_lead_id])
                : null,
            'stage_code' => $opportunity['stage_code'] ?? null,
            'stage_label' => $opportunity['stage_label'] ?? null,
        ]]);
    }

    public function store(SaveSiteRequest $request, SaveSite $saveSite, LineOaGroupChannel $line)
    {
        $site = $saveSite->create($request->validated(), $request->user());
        $line->dispatchPending();

        return redirect()->route('crm.admin.sites.index', ['site' => $site->id])->with('status', 'Site created.');
    }

    public function update(SaveSiteRequest $request, Site $site, SaveSite $saveSite, LineOaGroupChannel $line)
    {
        $saveSite->update($site, $request->validated(), $request->user());
        $line->dispatchPending();

        return redirect()->route('crm.admin.sites.index', ['site' => $site->id])->with('status', 'Site updated.');
    }
}
