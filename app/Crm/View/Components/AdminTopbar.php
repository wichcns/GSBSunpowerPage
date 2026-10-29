<?php

namespace App\Crm\View\Components;

use App\Crm\Services\CrmNotificationCenter;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class AdminTopbar extends Component
{
    public function __construct(public CrmNotificationCenter $center) {}

    public function render(): View
    {
        $userId = (int) Auth::guard('crm')->id();

        return view('crm.components.admin.topbar', [
            'notifications' => $userId > 0 ? $this->center->latest($userId) : collect(),
            'unreadCount' => $userId > 0 ? $this->center->unreadCount($userId) : 0,
        ]);
    }
}
