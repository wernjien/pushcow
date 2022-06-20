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
     * @var array
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
     * @param  array  $payload
     * @param  string  $deviceId
     * @param  string  $userId
     * @return void
     */
    public function __construct(array $payload, $deviceId, $userId)
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
        $notification = $this->payload->notification;
        $options = $this->payload->options;
        $deviceId = $this->deviceId;
        $data = $this->getData();

        $message = Message::create([
            'device_id' => $deviceId,
            'notification' => $notification,
            'data' => $data,
            'options' => $options,
        ]);

        ForwardMessage::dispatch($message);
    }

    /**
     * Process and return the message data.
     *
     * @return string
     */
    protected function getData()
    {
        $data = $this->payload->data;
        $userId = $this->userId;

        if (empty($userId)) {
            return $data;
        }

        $data = array_merge(['_user_id' => $userId], json_decode($data, true));

        return json_encode($data);
    }
}
