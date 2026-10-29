<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Actions\AssignSiteOwners;
use App\Crm\Http\Controllers\Controller;
use App\Crm\Models\Role;
use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\Services\LineOaGroupChannel;
use App\Crm\Support\SiteEditFingerprint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteOwnershipController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);
        $unassigned = Site::query()->whereNull('owner_user_id');
        $labels = (clone $unassigned)->whereNotNull('legacy_owner_label')
            ->where('legacy_owner_label', '!=', '')->selectRaw('legacy_owner_label, count(*) as total')
            ->groupBy('legacy_owner_label')->orderBy('legacy_owner_label')->get();
        $sites = $unassigned->with('contacts')
            ->when(! empty($filters['q']), fn ($query) => $query->where(function ($query) use ($filters) {
                $term = '%'.trim($filters['q']).'%';
                $query->where('name', 'like', $term)->orWhere('site_code', 'like', $term)
                    ->orWhere('legacy_owner_label', 'like', $term);
            }))
            ->when(! empty($filters['label']), fn ($query) => $query->where('legacy_owner_label', $filters['label']))
            ->orderBy('id')->paginate(25)->withQueryString();
        $users = User::query()->whereHas('role', fn ($query) => $query->where('slug', 'staff'))
            ->orderBy('name')->get(['id', 'name', 'email']);
        $userNames = $users->groupBy(fn (User $user) => mb_strtolower(trim($user->name)));

        return view('crm.admin.site-ownership', [
            'sites' => $sites,
            'labels' => $labels,
            'users' => $users,
            'candidateNames' => $sites->getCollection()->mapWithKeys(function (Site $site) use ($userNames) {
                $key = mb_strtolower(trim((string) $site->legacy_owner_label));

                return [$site->id => $key === '' ? [] : $userNames->get($key, collect())->pluck('name')->all()];
            }),
            'fingerprints' => $sites->getCollection()->mapWithKeys(fn (Site $site) => [$site->id => SiteEditFingerprint::for($site)]),
            'totalUnassigned' => Site::query()->whereNull('owner_user_id')->count(),
        ]);
    }

    public function assign(Request $request, AssignSiteOwners $assigner, LineOaGroupChannel $line)
    {
        $staffRoleId = Role::query()->where('slug', 'staff')->value('id');
        $data = $request->validate([
            'owner_user_id' => ['required', 'integer', Rule::exists('crm_users', 'id')->where('role_id', $staffRoleId)],
            'site_ids' => ['required', 'array', 'min:1', 'max:25'],
            'site_ids.*' => ['required', 'integer', 'distinct', Rule::exists('crm_sites', 'id')],
            'fingerprints' => ['required', 'array', 'max:25'],
            'fingerprints.*' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
        ]);

        $count = $assigner->assign($data['site_ids'], $data['fingerprints'], (int) $data['owner_user_id'], $request->user());
        $line->dispatchPending();

        return redirect()->route('crm.admin.ownership.index')->with('status', __('ownership.confirmed', ['count' => $count]));
    }
}
