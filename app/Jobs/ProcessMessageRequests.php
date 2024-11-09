<?php

namespace App\Jobs;

use App\MessageRequest;
use App\Support\StringParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

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
    public function __construct(protected MessageRequest $messageRequest)
    {
        $this->onQueue('message-requests');

        $this->hasTopicSupport = $messageRequest->application->hasTopicSupport();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->sendThroughTopic();
        $this->sendToDevices();

        $this->messageRequest->delete();
    }

    /**
     * Send through subscribed topic.
     */
    protected function sendThroughTopic(): void
    {
        $applicationId = $this->messageRequest->application_id;
        $payload = (object) [
            'notification' => $this->messageRequest->notification,
            'data' => $this->messageRequest->data,
            'options' => $this->messageRequest->options,
        ];

        if ($this->shouldSendThroughTopic()) {
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
            $applicationId = $this->messageRequest->application_id;
            $deviceUserPair = $devices->pluck('user_id', 'id');
            $payload = (object) [
                'notification' => $this->messageRequest->notification,
                'data' => $this->messageRequest->data,
                'options' => $this->messageRequest->options,
            ];

            foreach ($deviceUserPair as $deviceId => $userId) {
                try {
                    CreateMessage::dispatch(
                        payload: $payload,
                        applicationId: $applicationId,
                        topic: null,
                        deviceId: $deviceId,
                        userId: $userId
                    );
                } catch (Throwable $throwable) {
                    Log::channel('debug')->info(get_class(), [
                        'message' => $throwable->getMessage(),
                        'exception' => get_class($throwable),
                        'file' => $throwable->getFile(),
                        'line' => $throwable->getLine(),
                        'trace' => collect($throwable->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
                    ]);

                    continue;
                }
            }
        });
    }

    /**
     * Prepare the device builder for sending.
     */
    protected function prepareDeviceBuilder(): HasMany
    {
        $application = $this->messageRequest->application;
        $recipients = $this->messageRequest->recipients;

        return $application->devices()
            ->when($this->shouldSendThroughTopic(), function (Builder $query) {
                $query->whereDoesntHave('topics', function (Builder $query) {
                    $query->where('topic', 'global');
                });
            })
            ->search($recipients)
            ->orderBy('updated_at', 'desc');
    }

    /**
     * Determine whether should send through topic.
     */
    protected function shouldSendThroughTopic(): bool
    {
        return $this->hasTopicSupport && $this->shouldConsiderTopic();
    }

    /**
     * Determine whether should consider sending through topic.
     */
    protected function shouldConsiderTopic(): bool
    {
        $application = $this->messageRequest->application;
        $recipients = $this->messageRequest->recipients;

        if ($recipients == '*') {
            return true;
        }

        $parsedRecipients = StringParser::auto($recipients);

        if (is_array($parsedRecipients) && Arr::has($parsedRecipients, 'except')) {
            $shouldSendIndividually = $application->devices()
                ->when($this->hasTopicSupport, function (Builder $query) {
                    $query->whereDoesntHave('topics', function (Builder $query) {
                        $query->where('topic', 'global');
                    });
                })
                ->search($recipients)
                ->count();

            if (! $shouldSendIndividually) {
                return true;
            }
        }

        return false;
    }
}
