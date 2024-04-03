<?php

namespace App\Services;

use App\Device;
use App\Message;
use Illuminate\Support\Arr;

abstract class PushNotificationService
{
    /**
     * Push notification.
     */
    abstract public function push(Message $message): void;

    /**
     * Subscribe device to a topic.
     */
    abstract public function subscribeToTopic(Device $device, string $topic): void;

    /**
     * Invoke boot function before push or subscribe.
     */
    public function __call(string $name, array $arguments): mixed
    {
        $methods = ['push', 'subscribeToTopic'];

        if (in_array($name, $methods) && is_callable([$this, 'boot'])) {
            $application = Arr::get($arguments, '0.application');

            call_user_func_array([$this, 'boot'], [$application]);
        }

        return call_user_func_array([$this, $name], $arguments);
    }
}
