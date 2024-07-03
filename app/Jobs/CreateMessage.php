<?php

namespace App\Jobs;

use App\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected object $payload,
        protected int $applicationId,
        protected ?string $topic,
        protected ?string $deviceId,
        protected ?string $userId
    ) {
        $this->onQueue('create-message');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notification = $this->getNotification();
        $data = $this->getData();
        $options = $this->getOptions();

        try {
            $exists = DB::table('messages')
                ->where('application_id', $this->applicationId)
                ->where('topic', $this->topic)
                ->where('device_id', $this->deviceId)
                ->whereJsonContains('notification', $notification)
                ->whereJsonContains('data', $data)
                ->whereJsonContains('options', $options)
                ->exists();

            if ($exists) {
                return;
            }
        } catch (Throwable $throwable) {
            Log::error([
                'message' => $throwable->getMessage(),
                'exception' => get_class($throwable),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => collect($throwable->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
            ]);
        }

        $message = Message::create([
            'application_id' => $this->applicationId,
            'topic' => $this->topic,
            'device_id' => $this->deviceId,
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
