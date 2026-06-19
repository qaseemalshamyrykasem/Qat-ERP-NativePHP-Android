<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

/**
 * NotificationService — in-app notifications.
 * For Laravel-native notifications, see \Illuminate\Notifications\Notifiable trait on User.
 */
class NotificationService
{
    public function send(int $userId, string $type, string $title, ?string $message = null, ?string $refType = null, ?int $refId = null): AppNotification
    {
        return AppNotification::create([
            'user_id'       => $userId,
            'type'          => $type,
            'title'         => $title,
            'message'       => $message,
            'reference_type'=> $refType,
            'reference_id'  => $refId,
        ]);
    }

    public function sendToRole(string $role, string $type, string $title, ?string $message = null): int
    {
        $count = 0;
        User::where('role', $role)->where('status', true)->chunk(200, function ($users) use ($type, $title, $message, &$count) {
            foreach ($users as $user) {
                $this->send($user->id, $type, $title, $message);
                $count++;
            }
        });
        return $count;
    }

    public function unreadCount(int $userId): int
    {
        return AppNotification::where('user_id', $userId)->where('is_read', false)->count();
    }

    public function markRead(int $notificationId): void
    {
        AppNotification::where('id', $notificationId)->where('user_id', auth()->id())
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function markAllRead(int $userId): void
    {
        AppNotification::where('user_id', $userId)->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
