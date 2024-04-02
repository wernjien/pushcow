<?php

namespace App\Jobs;

use App\MessageRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMessageRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected MessageRequest $request)
    {
        $this->onQueue('message-requests');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendThroughTopic();
        $this->sendToDevices();

        $this->request->delete();
    }

    /**
     * Send through subscribed topic.
     */
    protected function sendThroughTopic()
    {
        $application = $this->request->application;
        $applicationId = $this->request->application_id;
        $recipients = $this->request->recipients;
        $payload = (object) [
            'notification' => $this->request->notification,
            'data' => $this->request->data,
            'options' => $this->request->options,
        ];

        if ($application->hasTopicSupport() && $recipients == '*') {
            CreateMessage::dispatch($payload, $applicationId, 'global', null, null);
        }
    }

    /**
     * Send to individual devices.
     */
    protected function sendToDevices()
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
                CreateMessage::dispatch($payload, $applicationId, null, $deviceId, $userId);
            }
        });
    }

    /**
     * Prepare the device builder for sending.
     */
    protected function prepareDeviceBuilder(): Builder
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
