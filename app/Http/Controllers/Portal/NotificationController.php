<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(20);

        return view('portal.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->where('id', $id)->firstOrFail()->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->where('id', $id)->firstOrFail()->delete();

        return back();
    }
}
