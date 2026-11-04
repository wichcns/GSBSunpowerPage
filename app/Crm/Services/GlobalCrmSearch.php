<?php

namespace App\Crm\Services;

use App\Crm\Models\Site;
use App\Crm\Models\User;
use App\Crm\ReadModels\LegacyContracts;
use App\Crm\ReadModels\LegacyPipeline;
use App\Crm\Support\AssignedCrmRecords;

class GlobalCrmSearch
{
    public function __construct(
        private readonly LegacyPipeline $pipeline,
        private readonly LegacyContracts $contracts,
        private readonly AssignedCrmRecords $assigned,
    ) {}

    public function for(User $user, string $query): array
    {
        $needle = mb_strtolower($query);
        $results = [];

        if ($user->hasPermission('sites.view')) {
            $term = '%'.$query.'%';
            $results['sites'] = Site::query()->with('contacts')
                ->where(function ($sites) use ($term) {
                    $sites->where('name', 'like', $term)->orWhere('site_code', 'like', $term)->orWhere('area', 'like', $term)
                        ->orWhereHas('contacts', fn ($contacts) => $contacts->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)->orWhere('line_name', 'like', $term)->orWhere('email', 'like', $term));
                })->latest()->limit(10)->get();
        }

        if ($user->hasPermission('pipeline.view')) {
            $rows = $this->pipeline->all();
            if ($user->role?->slug === 'staff') {
                $rows = array_intersect_key($rows, array_flip($this->assigned->pipeline($user)));
            }
            $results['pipeline'] = $this->matchingRows($rows, $needle);
        }

        if ($user->hasPermission('contracts.view')) {
            $results['contracts'] = $this->matchingRows($this->contracts->all(), $needle);
        }

        return $results;
    }

    private function matchingRows(array $rows, string $needle): array
    {
        return array_slice(array_values(array_filter($rows, static fn (array $row): bool => str_contains(
            mb_strtolower(implode(' ', [$row['site_name'] ?? '', $row['site_code'] ?? '', $row['area'] ?? ''])),
            $needle,
        ))), 0, 10);
    }
}
