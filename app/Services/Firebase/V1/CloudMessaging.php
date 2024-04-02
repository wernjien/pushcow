<?php

namespace App\Services\Firebase\V1;

use App\Device;
use App\Message;
use App\Services\PushNotificationService;
use App\Topic;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/**
 * @see https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html
 */
class CloudMessaging extends PushNotificationService
{
    /**
     * The messaging service.
     */
    protected Messaging $service;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(public Device $device)
    {
        $factory = (new Factory)->withServiceAccount(
            $this->getServiceAccountPrivateKeyPath()
        );

        $this->service = $factory->createMessaging();
    }

    /**
     * Push notification.
     */
    public function push(Message $message): void
    {
        $target = ['token', data_get($message, 'device.token')];
        $title = data_get($message, 'notification.title');
        $body = data_get($message, 'notification.body');
        $data = data_get($message, 'data');

        $this->send($target, $title, $body, $data);
    }

    /**
     * Send a push notification.
     */
    public function send(array $target, string $title, string $body, array $data = []): void
    {
        $message = CloudMessage::withTarget(...$target)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $this->service->send($message);
    }

    /**
     * Subscribe device to a topic.
     */
    public function subscribeToTopic(string $topic): void
    {
        $this->service->subscribeToTopic($topic, $this->device->token);

        $this->device->topics()->create(['topic' => $topic]);
    }

    /**
     * Get the service account private key path.
     */
    protected function getServiceAccountPrivateKeyPath(): string
    {
        $serviceAccount = data_get($this->device->application, 'service_account');

        return storage_path("service-accounts/{$serviceAccount}.json");
    }
}
