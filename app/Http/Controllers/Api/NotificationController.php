<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated user.
     * GET /api/notifications
     *
     * Query params:
     *   unread=1  — return only unread notifications
     *   per_page  — items per page (default 20, max 50)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 50);

        $query = $request->user()
            ->notifications()
            ->when($request->boolean('unread'), fn ($q) => $q->whereNull('read_at'));

        $notifications = $query->paginate($perPage);

        return response()->json([
            'data'       => $notifications->items(),
            'meta'       => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * Mark a single notification as read.
     * POST /api/notifications/{id}/read
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Mark all notifications as read.
     * POST /api/notifications/read-all
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    /**
     * Delete a notification.
     * DELETE /api/notifications/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail()
            ->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
