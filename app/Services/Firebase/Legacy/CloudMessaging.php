<?php

namespace App\Services\Firebase\Legacy;

use App\Device;
use App\Message;
use App\Services\PushNotificationService;
use Illuminate\Support\Str;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Response\DownstreamResponse;

class CloudMessaging extends PushNotificationService
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
     * Push notification.
     */
    public function push(Message $message): void
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
     * Send a downstream message.
     */
    protected function send(Message $message): DownstreamResponse
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
     */
    protected function builder(OptionsBuilder|PayloadNotificationBuilder $object, array|string $parameters = []): void
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
     */
    protected function updateMessageStatus(Message $message, DownstreamResponse $response): void
    {
        $message->status = ($response->numberSuccess() > 0)
            ? Message::STATUS_SUCCESS : Message::STATUS_FAILED;

        $message->save();
    }

    /**
     * Update the device token.
     */
    protected function updateDeviceToken(Device $device, DownstreamResponse $response): void
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
