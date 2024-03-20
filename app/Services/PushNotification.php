<?php

namespace App\Services;

use App\Device;
use App\Message;
use App\Services\Firebase\CloudMessagingService;
use Cache;
use FCM;
use Illuminate\Support\Str;
use Innoractive\HuaweiPushService\HuaweiPushService;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

class PushNotification
{
    /**
     * The options builder.
     *
     * @var \LaravelFCM\Message\OptionsBuilder
     */
    protected $options;

    /**
     * The notification builder.
     *
     * @var \LaravelFCM\Message\PayloadNotificationBuilder
     */
    protected $notification;

    /**
     * The data builder.
     *
     * @var \LaravelFCM\Message\PayloadDataBuilder
     */
    protected $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->options = new OptionsBuilder;
        $this->notification = new PayloadNotificationBuilder;
        $this->data = new PayloadDataBuilder;
    }

    /**
     * Send a push notification.
     */
    public function push($message): void
    {
        if ($message->isHuaweiPushService()) {
            $this->pushToHuaweiPushService($message);
        } else {
            if ($message->isFcmHttpV1()) {
                $this->pushToFcmHttpV1($message);
            } else {
                $this->pushToFcmLegacy($message);
            }
        }
    }

    /**
     * Send a push notification to FCM HTTP V1.
     */
    protected function pushToFcmHttpV1($message): void
    {
        $service = new CloudMessagingService;

        // $service->send(...);
    }

    /**
     * Send a push notification to FCM Legacy.
     */
    protected function pushToFcmLegacy($message): void
    {
        $response = $this->send($message);

        if (count($response->tokensToRetry()) == 0) {
            $this->updateMessageStatus($message, $response);
        }

        if ($response->numberModification() > 0) {
            $this->updateDeviceToken($message->device, $response);
        }
    }

    /**
     * Send a push notification to Huawei Push Service.
     */
    protected function pushToHuaweiPushService($message): void
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

        $response = HuaweiPushService::sendNotification($clientId, $accessToken, $title, $body, $token);

        $message->status = (data_get($response, 'code') == '80000000')
            ? Message::STATUS_SUCCESS : Message::STATUS_FAILED;

        $message->save();
    }

    /**
     * Send a downstream message.
     *
     * @param  \App\Message  $message
     * @return \LaravelFCM\Response\DownstreamResponse
     */
    protected function send($message)
    {
        $serverKey = data_get($message, 'device.application.server_key');
        $senderId = data_get($message, 'device.application.sender_id');
        $token = data_get($message, 'device.token');
        $notification = data_get($message, 'notification');
        $data = data_get($message, 'data', []);
        $options = data_get($message, 'options');

        config(['fcm.http.server_key' => $serverKey]);
        config(['fcm.http.sender_id' => $senderId]);

        $this->builder($this->options, $options);
        $this->builder($this->notification, $notification);

        $options = $this->options->build();
        $notification = $this->notification->build();
        $data = $this->data->addData($data)->build();

        return FCM::sendTo($token, $options, $notification, $data);
    }

    /**
     * Set the parameters to the given object.
     *
     * @param  object  $object
     * @param  mixed  $parameters
     * @return void
     */
    protected function builder($object, $parameters = [])
    {
        if (is_string($parameters)) {
            $parameters = json_decode($parameters) ?: [];
        }

        foreach ($parameters as $key => $value) {
            $method = 'set'.Str::studly($key);

            if (! is_array($value)) {
                $value = [$value];
            }

            call_user_func_array([$object, $method], $value);
        }
    }

    /**
     * Update the message status.
     *
     * @param  \App\Message  $message
     * @param  \LaravelFCM\Response\DownstreamResponse  $response
     * @return void
     */
    protected function updateMessageStatus($message, $response)
    {
        $message->status = ($response->numberSuccess() > 0)
            ? Message::STATUS_SUCCESS : Message::STATUS_FAILED;

        $message->save();
    }

    /**
     * Update the device token.
     *
     * @param  \App\Device  $device
     * @param  \LaravelFCM\Response\DownstreamResponse  $response
     * @return void
     */
    protected function updateDeviceToken($device, $response)
    {
        if (count($response->tokensToDelete()) > 0 ||
            count($response->tokensWithError()) > 0
        ) {
            $device->delete();
        }

        if (key($response->tokensToModify()) == $device->token) {
            $device->token = current($response->tokensToModify());

            $device->save();
        }
    }
}
