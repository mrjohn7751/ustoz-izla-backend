<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send elon approved notification
     */
    public function sendElonApprovedNotification(int $userId, array $data): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'elon_approved',
                'title' => 'E\'lon tasdiqlandi',
                'message' => "Sizning \"{$data['title']}\" e'loningiz tasdiqlandi va saytda faol!",
                'data' => $data,
            ]);

            // Send push notification if FCM token exists
            $this->sendPushNotification($userId, 'E\'lon tasdiqlandi', "Sizning e'loningiz tasdiqlandi!");
        } catch (\Exception $e) {
            Log::error('Failed to send elon approved notification: ' . $e->getMessage());
        }
    }

    /**
     * Send elon rejected notification
     */
    public function sendElonRejectedNotification(int $userId, array $data): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'elon_rejected',
                'title' => 'E\'lon rad etildi',
                'message' => "Sizning \"{$data['title']}\" e'loningiz rad etildi.",
                'data' => $data,
            ]);

            // Send push notification
            $this->sendPushNotification($userId, 'E\'lon rad etildi', "E'loningizni qayta ko'rib chiqing");
        } catch (\Exception $e) {
            Log::error('Failed to send elon rejected notification: ' . $e->getMessage());
        }
    }

    /**
     * Send video approved notification
     */
    public function sendVideoApprovedNotification(int $userId, array $data): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'video_approved',
                'title' => 'Video tasdiqlandi',
                'message' => "Sizning \"{$data['title']}\" videongiz tasdiqlandi!",
                'data' => $data,
            ]);

            $this->sendPushNotification($userId, 'Video tasdiqlandi', "Videongiz saytda faol!");
        } catch (\Exception $e) {
            Log::error('Failed to send video approved notification: ' . $e->getMessage());
        }
    }

    /**
     * Send video rejected notification
     */
    public function sendVideoRejectedNotification(int $userId, array $data): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'video_rejected',
                'title' => 'Video rad etildi',
                'message' => "Sizning \"{$data['title']}\" videongiz rad etildi.",
                'data' => $data,
            ]);

            $this->sendPushNotification($userId, 'Video rad etildi', "Videoni qayta yuklang");
        } catch (\Exception $e) {
            Log::error('Failed to send video rejected notification: ' . $e->getMessage());
        }
    }

    /**
     * Send new comment notification
     */
    public function sendNewCommentNotification(int $userId, array $data): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'new_comment',
                'title' => 'Yangi izoh',
                'message' => "{$data['user_name']} sizning e'loningizga izoh qoldirdi",
                'data' => $data,
            ]);

            $this->sendPushNotification($userId, 'Yangi izoh', "E'loningizga yangi izoh!");
        } catch (\Exception $e) {
            Log::error('Failed to send new comment notification: ' . $e->getMessage());
        }
    }

    /**
     * Send new rating notification
     */
    public function sendNewRatingNotification(int $userId, array $data): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'new_rating',
                'title' => 'Yangi baho',
                'message' => "{$data['user_name']} sizga {$data['rating']} ⭐ baho berdi",
                'data' => $data,
            ]);

            $this->sendPushNotification($userId, 'Yangi baho', "Sizga yangi baho berildi!");
        } catch (\Exception $e) {
            Log::error('Failed to send new rating notification: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification via Firebase Cloud Messaging
     */
    private function sendPushNotification(int $userId, string $title, string $body): void
    {
        try {
            $user = User::find($userId);
            
            if (!$user || !$user->fcm_token) {
                return;
            }

            // TODO: Implement Firebase Cloud Messaging
            // This is a placeholder for FCM integration
            
            /*
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
            
            $messaging = app('firebase.messaging');
            $messaging->send($message);
            */
            
            Log::info("Push notification sent to user {$userId}: {$title}");
        } catch (\Exception $e) {
            Log::error('Failed to send push notification: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notifications count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function deleteOldNotifications(): int
    {
        return Notification::where('created_at', '<', now()->subDays(30))->delete();
    }
}
