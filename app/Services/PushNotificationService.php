<?php

namespace App\Services;

use App\Device;
use App\Message;

abstract class PushNotificationService
{
    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(public Device $device) {}

    /**
     * Push notification.
     */
    abstract public function push(Message $message): void;

    /**
     * Subscribe device to a topic.
     */
    abstract public function subscribeToTopic(string $topic): void;
}
