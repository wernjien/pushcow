<?php

namespace App\Jobs;

use App\Message;
use App\Jobs\ForwardMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The notification payload.
     *
     * @var object
     */
    protected $payload;

    /**
     * The recipient device ID.
     *
     * @var string
     */
    protected $deviceId;

    /**
     * The recipient user's ID.
     *
     * @var string
     */
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param  object  $payload
     * @param  string  $deviceId
     * @param  string  $userId
     * @return void
     */
    public function __construct($payload, $deviceId, $userId)
    {
        $this->onQueue('create-message');

        $this->payload = $payload;
        $this->deviceId = $deviceId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $deviceId = $this->deviceId;
        $notification = $this->getNotification();
        $data = $this->getData();
        $options = $this->getOptions();

        $message = Message::create([
            'device_id' => $deviceId,
            'notification' => $notification,
            'data' => $data,
            'options' => $options,
        ]);

        ForwardMessage::dispatch($message);
    }

    /**
     * Get the notification payload.
     *
     * @return array
     */
    protected function getNotification()
    {
        return json_decode($this->payload->notification) ?: (object) [];
    }

    /**
     * Get the data payload.
     *
     * @return array
     */
    protected function getData()
    {
        $data = json_decode($this->payload->data, true) ?: [];
        $userId = $this->userId;

        if (! empty($userId)) {
            $data = array_merge(['_user_id' => $userId], $data);
        }

        return (object) $data;
    }

    /**
     * Get the options payload.
     *
     * @return array
     */
    protected function getOptions()
    {
        return json_decode($this->payload->options) ?: (object) [];
    }
}
