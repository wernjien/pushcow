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
        $service = app(PushNotificationService::class, $message->device);

        $service->push($message);
    }
}
