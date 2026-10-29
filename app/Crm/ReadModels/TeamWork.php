<?php

namespace App\Crm\ReadModels;

use App\Crm\Models\User;

class TeamWork
{
    public function __construct(private readonly MyWork $work) {}

    public function forStaff(): array
    {
        return User::query()->whereHas('role', fn ($query) => $query->where('slug', 'staff'))
            ->orderBy('name')->get(['id', 'name', 'email'])
            ->map(function (User $user): array {
                $items = $this->work->forUser($user->id);

                return [
                    'user' => $user,
                    'sites' => $this->work->sitesForUser($user->id),
                    'items' => $items,
                    'overdue' => count(array_filter($items, fn (array $item) => $item['overdue'])),
                ];
            })->all();
    }
}
