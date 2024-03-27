<?php

namespace App\Services;

use App\Message;

class PushNotification
{
    /**
     * Send a push notification.
     */
    public static function push(Message $message): void
    {
        $device = $message->device;
        $service = app(PushNotificationService::class, compact('device'));

        $service->push($message);
    }
}
