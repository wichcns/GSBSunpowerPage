<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use Illuminate\Http\Request;

class SiteDirectory
{
    public function __construct(private readonly SiteContactPreview $contactPreview) {}

    public function forRequest(Request $request): array
    {
        $sites = Site::query()->with(['owner', 'contacts'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)->orWhere('site_code', 'like', $term)
                        ->orWhere('area', 'like', $term)
                        ->orWhereHas('contacts', fn ($query) => $query->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhere('line_name', 'like', $term)
                            ->orWhere('email', 'like', $term));
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

        return [
            'sites' => $sites,
            'areas' => Site::whereNotNull('area')->distinct()->orderBy('area')->pluck('area'),
            'types' => Site::whereNotNull('location_type')->distinct()->orderBy('location_type')->pluck('location_type'),
            'owners' => User::orderBy('name')->get(['id', 'name']),
            'contactPreviews' => $this->contactPreview->forSites($sites->getCollection()),
        ];
    }
}
