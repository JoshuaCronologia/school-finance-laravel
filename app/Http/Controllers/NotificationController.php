<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get recent notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user() ? $request->user()->id : session('user_id');

        if (!$userId) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $notifications = Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($n) { return [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'url' => $n->data['url'] ?? null,
                'read_at' => ($n->read_at ? $n->read_at->toIso8601String() : null),
                'created_at' => $n->created_at->toIso8601String(),
                'time_ago' => $n->created_at->diffForHumans(),
            ]; });

        $unreadCount = Notification::where('user_id', $userId)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $userId = $request->user() ? $request->user()->id : session('user_id');
        if ($notification->user_id !== $userId) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $userId = $request->user() ? $request->user()->id : session('user_id');
        Notification::where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
