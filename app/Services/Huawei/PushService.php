<?php

namespace App\Services\Huawei;

use App\Message;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Cache;
use Innoractive\HuaweiPushService\HuaweiPushService;
use RuntimeException;

class PushService extends PushNotificationService
{
    /**
     * Constant representing a successful HPS response code.
     *
     * @var string
     */
    const RESPONSE_CODE_SUCCESS = '80000000';

    /**
     * Push notification.
     */
    protected function push(Message $message): void
    {
        $response = $this->send($message);

        if (data_get($response, 'code') != static::RESPONSE_CODE_SUCCESS) {
            throw new RuntimeException(
                data_get($response, 'msg', 'Huawei Push Service failed to deliver the message.')
            );
        }
    }

    /**
     * Subscribe device to a topic.
     */
    protected function subscribeToTopic(string $topic): void {}

    /**
     * Send a downstream message.
     */
    protected function send(Message $message): object
    {
        $clientId = data_get($message, 'device.application.hps_client_id');
        $clientSecret = data_get($message, 'device.application.hps_client_secret');
        $cacheKey = "hps.{$clientId}";

        if (Cache::has($cacheKey)) {
            $accessToken = Cache::get($cacheKey);
        } else {
            $accessToken = HuaweiPushService::getAccessToken($clientId, $clientSecret);

            Cache::put($cacheKey, $accessToken, now()->addMinutes(10));
        }

        $title = data_get($message, 'notification.title');
        $body = data_get($message, 'notification.body');
        $token = data_get($message, 'device.token');

        return HuaweiPushService::sendNotification($clientId, $accessToken, $title, $body, $token);
    }
}
