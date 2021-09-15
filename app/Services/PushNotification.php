<?php

namespace App\Services;

use FCM;
use Cache;
use App\Message;
use Illuminate\Support\Str;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Innoractive\HuaweiPushService\HuaweiPushService;

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
     * Send push notifications.
     *
     * @return void
     */
    public function push()
    {
        $this->pushToFcm();
        $this->pushToHuaweiPushService();
    }

    /**
     * Send push notifications to FCM.
     *
     * @return void
     */
    protected function pushToFcm()
    {
        $messages = Message::toFcm()
            ->where('status', Message::STATUS_PENDING)
            ->oldest()
            ->take(240)
            ->get();

        foreach ($messages as $message) {
            $response = $this->send($message);

            if (count($response->tokensToRetry()) == 0) {
                $this->updateMessageStatus($message, $response);
            }

            if ($response->numberModification() > 0) {
                $this->updateDeviceToken($message->device, $response);
            }
        }
    }

    /**
     * Send push notifications to Huawei Push Service.
     *
     * @return void
     */
    protected function pushToHuaweiPushService()
    {
        $messages = Message::toHuaweiPushService()
            ->where('status', Message::STATUS_PENDING)
            ->oldest()
            ->take(240)
            ->get();

        foreach ($messages as $message) {
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
