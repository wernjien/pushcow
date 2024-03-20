<?php

namespace App\Services\Firebase\V1;

use App\Services\PushNotificationContract;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/**
 * @see https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html
 */
class CloudMessagingService implements PushNotificationContract
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
    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(
            $this->getServiceAccountPrivateKeyPath()
        );

        $this->service = $factory->createMessaging();
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
     * Get the service account private key path.
     */
    protected function getServiceAccountPrivateKeyPath(): string
    {
        $serviceAccount = data_get(auth()->user(), 'service_account');

        return storage_path("service-accounts/{$serviceAccount}.json");
    }
}
