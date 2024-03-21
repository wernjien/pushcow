<?php

namespace App\Services\Huawei;

use App\Message;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Cache;
use Innoractive\HuaweiPushService\HuaweiPushService;

class PushService extends PushNotificationService
{
    /**
     * Push notification.
     */
    public function push(Message $message): void
    {
        $response = $this->send($message);

        $this->updateMessageStatus($message, $response);
    }

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

    /**
     * Update the message status.
     */
    protected function updateMessageStatus(Message $message, object $response): void
    {
        $message->status = (data_get($response, 'code') == '80000000')
            ? Message::STATUS_SUCCESS : Message::STATUS_FAILED;

        $message->save();
    }
}
