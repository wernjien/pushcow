<?php

namespace App\Jobs;

use App\Message;
use App\MessageRequest;
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
     * The message request instance.
     *
     * @var \App\MessageRequest
     */
    protected $request;

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
     * @param  \App\MessageRequest  $request
     * @param  string  $deviceId
     * @param  string  $userId
     * @return void
     */
    public function __construct(MessageRequest $request, $deviceId, $userId)
    {
        $this->onQueue('create-message');

        $this->request = $request;
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
        $request = $this->request;
        $deviceId = $this->deviceId;
        $data = $this->getData();

        $message = Message::create([
            'device_id' => $deviceId,
            'notification' => $request->notification,
            'data' => $data,
            'options' => $request->options,
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
        $data = $this->request->data;
        $userId = $this->userId;

        if (empty($userId)) {
            return $data;
        }

        $data = array_merge(['_user_id' => $userId], json_decode($data, true));

        return json_encode($data);
    }
}
