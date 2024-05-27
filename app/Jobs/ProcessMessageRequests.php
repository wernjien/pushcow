<?php

namespace App\Jobs;

use App\MessageRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMessageRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Flag to indicates whether sending to topic is supported.
     */
    protected bool $hasTopicSupport;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected MessageRequest $request)
    {
        $this->onQueue('message-requests');

        $this->hasTopicSupport = $request->application->hasTopicSupport();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->sendThroughTopic();
        $this->sendToDevices();
    }

    /**
     * Send through subscribed topic.
     */
    protected function sendThroughTopic(): void
    {
        $applicationId = $this->request->application_id;
        $recipients = $this->request->recipients;
        $payload = (object) [
            'notification' => $this->request->notification,
            'data' => $this->request->data,
            'options' => $this->request->options,
        ];

        if ($this->hasTopicSupport && $recipients == '*') {
            CreateMessage::dispatch(
                payload: $payload,
                applicationId: $applicationId,
                topic: 'global',
                deviceId: null,
                userId: null
            );
        }
    }

    /**
     * Send to individual devices.
     */
    protected function sendToDevices(): void
    {
        $this->prepareDeviceBuilder()->chunk(500, function ($devices) {
            $applicationId = $this->request->application_id;
            $deviceUserPair = $devices->pluck('user_id', 'id');
            $payload = (object) [
                'notification' => $this->request->notification,
                'data' => $this->request->data,
                'options' => $this->request->options,
            ];

            foreach ($deviceUserPair as $deviceId => $userId) {
                CreateMessage::dispatch(
                    payload: $payload,
                    applicationId: $applicationId,
                    topic: null,
                    deviceId: $deviceId,
                    userId: $userId
                );
            }
        });
    }

    /**
     * Prepare the device builder for sending.
     */
    protected function prepareDeviceBuilder(): HasMany
    {
        $application = $this->request->application;
        $recipients = $this->request->recipients;

        return $application->devices()
            ->when($application->hasTopicSupport(), function (Builder $query) {
                $query->whereDoesntHave('topics', function (Builder $query) {
                    $query->where('topic', 'global');
                });
            })
            ->search($recipients)
            ->orderBy('updated_at', 'desc');
    }
}
