<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\Users\BranchUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Resolve the current user's notification ID.
     * Standard auth users: user.id | SSO/branch users: branch_user.id
     */
    private function resolveUserId(Request $request): ?string
    {
        // Standard Laravel auth (admin users)
        if ($request->user()) {
            return (string) $request->user()->id;
        }

        // SSO: try branch_user_id from session first
        $info = session('user_info');
        if ($info) {
            if (!empty($info['branch_user_id'])) {
                return (string) $info['branch_user_id'];
            }
            if (!empty($info['branch_account']) && is_object($info['branch_account'])) {
                return (string) $info['branch_account']->id;
            }
        }

        // Final fallback: look up BranchUser from session user_id + branch_code
        $parentId = session('user_id');
        $branchCode = session('branch_code');
        if ($parentId && $branchCode) {
            $bu = BranchUser::where('parent_id', $parentId)
                ->where('branch_code', $branchCode)
                ->where('is_active', true)
                ->first();
            if ($bu) {
                return (string) $bu->id;
            }
        }

        return null;
    }

    /**
     * Get recent notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

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
        $userId = $this->resolveUserId($request);
        if ((string) $notification->user_id !== $userId) {
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
        $userId = $this->resolveUserId($request);
        Notification::where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
