<?php

namespace App\Services;

use App\Device;
use App\Message;
use Error;

abstract class PushNotificationService
{
    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(public ?Device $device = null) {}

    /**
     * Push notification.
     */
    abstract protected function push(Message $message): void;

    /**
     * Subscribe device to a topic.
     */
    abstract protected function subscribeToTopic(string $topic): void;

    /**
     * Invoke boot function before push or subscribe.
     */
    public function __call(string $name, array $arguments): mixed
    {
        $allowedMethods = ['push', 'subscribeToTopic'];

        if (in_array($name, $allowedMethods)) {
            if (method_exists($this, 'boot') && is_callable([$this, 'boot'])) {
                $model = ($this->device?->exists()) ? $this->device : reset($arguments);
                $application = data_get($model, 'application');

                call_user_func_array([$this, 'boot'], [$application]);
            }

            return call_user_func_array([$this, $name], $arguments);
        }

        $callingMethod = get_class($this)."::{$name}()";

        throw new Error("Call to protected method {$callingMethod} from global scope.");
    }
}
