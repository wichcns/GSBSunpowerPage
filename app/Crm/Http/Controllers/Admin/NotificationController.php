<?php

namespace App\Crm\Http\Controllers\Admin;

use App\Crm\Http\Controllers\Controller;
use App\Crm\Services\CrmNotificationCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request, CrmNotificationCenter $notifications): View
    {
        return view('crm.admin.notifications', [
            'title' => __('notifications.title'),
            'notifications' => $notifications->history($request->user()->id),
            'unreadCount' => $notifications->unreadCount($request->user()->id),
        ]);
    }

    public function open(int $notification, Request $request, CrmNotificationCenter $notifications): RedirectResponse
    {
        $url = $notifications->open($request->user()->id, $notification);
        abort_if($url === null, 404);

        return redirect()->to($url);
    }

    public function readAll(Request $request, CrmNotificationCenter $notifications): RedirectResponse
    {
        $notifications->markAllRead($request->user()->id);

        return back()->with('status', __('notifications.all_read_status'));
    }
}
