<?php

namespace App\Services;

use FCM;
use App\Message;
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
     * Send push notifications.
     *
     * @return void
     */
    public function push()
    {
        $messages = Message::where('status', Message::STATUS_PENDING)
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
     * Send a downstream message.
     *
     * @param  \App\Message  $message
     * @return \LaravelFCM\Response\DownstreamResponse
     */
    protected function send($message)
    {
        $token = data_get($message, 'device.token');
        $title = data_get($message, 'notification.title');
        $body = data_get($message, 'notification.body');
        $data = data_get($message, 'data', []);

        $options = $this->options->build();
        $notification = $this->notification->setTitle($title)->setBody($body)->build();
        $data = $this->data->addData($data)->build();

        return FCM::sendTo($token, $options, $notification, $data);
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
