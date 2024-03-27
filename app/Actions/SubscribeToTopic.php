<?php

namespace App\Actions;

use App\Device;
use App\Services\Firebase\V1\CloudMessaging;
use Spatie\QueueableAction\QueueableAction;

class SubscribeToTopic
{
    use QueueableAction;

    /**
     * Create a new action instance.
     *
     * @return void
     */
    public function __construct(
        protected CloudMessaging $service
    ) {}

    /**
     * Subscribe device to a topic.
     */
    public function execute(Device $device, string $topic): void
    {
        $this->service->subscribeToTopic($device->token, $topic);
    }
}
