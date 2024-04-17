<?php

namespace App\Services\Firebase\V1;

use App\Application;
use App\Message;
use App\Services\PushNotificationService;
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
     * Push notification.
     */
    protected function push(Message $message): void
    {
        $target = $this->getTarget($message);
        $title = data_get($message, 'notification.title');
        $body = data_get($message, 'notification.body');
        $data = data_get($message, 'data');

        $this->send($target, $title, $body, $data);
    }

    /**
     * Subscribe device to a topic.
     */
    protected function subscribeToTopic(string $topic): void
    {
        $this->service->subscribeToTopic($topic, $this->device->token);

        $this->device->topics()->create(['topic' => $topic]);
    }

    /**
     * Bootstrap the core service.
     */
    protected function boot(Application $application): void
    {
        $factory = (new Factory)->withServiceAccount(
            $this->getServiceAccountPrivateKeyPath($application)
        );

        $this->service = $factory->createMessaging();
    }

    /**
     * Send a push notification.
     */
    protected function send(array $target, string $title, string $body, array $data = []): void
    {
        $message = CloudMessage::withTarget(...$target)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $this->service->send($message);
    }

    /**
     * Get the service account private key path.
     */
    protected function getServiceAccountPrivateKeyPath(Application $application): string
    {
        $serviceAccount = data_get($application, 'service_account');

        return storage_path("service-accounts/{$serviceAccount}.json");
    }

    /**
     * Get the message target.
     */
    protected function getTarget(Message $message): array
    {
        if (! empty($message->topic)) {
            return ['topic', $message->topic];
        }

        return ['token', $message->device->token];
    }
}
