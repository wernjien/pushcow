<?php

namespace App\Services;

use App\Message;

abstract class PushNotificationService
{
    /**
     * Push notification.
     */
    abstract public function push(Message $message): void;
}
