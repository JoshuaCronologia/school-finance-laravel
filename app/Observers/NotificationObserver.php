<?php

namespace App\Observers;

use App\Models\Notification;

class NotificationObserver
{
    public function created(Notification $notification): void
    {
        // Clear user's notification cache when new notification arrives
        cache()->forget("notif_list_{$notification->user_id}");
        cache()->forget("notif_unread_{$notification->user_id}");
    }

    public function updated(Notification $notification): void
    {
        // Clear cache when notification is marked as read
        cache()->forget("notif_list_{$notification->user_id}");
        cache()->forget("notif_unread_{$notification->user_id}");
    }
}
