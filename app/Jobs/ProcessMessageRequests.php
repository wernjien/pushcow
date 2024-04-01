<?php

namespace App\Jobs;

use App\MessageRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMessageRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The message request instance.
     *
     * @var \App\MessageRequest
     */
    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MessageRequest $request)
    {
        $this->onQueue('message-requests');

        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;
        $application = $request->application;
        $devices = $application->devices()
            ->search($request->recipients)
            ->orderBy('updated_at', 'desc');

        $devices->chunk(500, function ($devices) use ($request) {
            $deviceUserPair = $devices->pluck('user_id', 'id');
            $payload = (object) [
                'notification' => $request->notification,
                'data' => $request->data,
                'options' => $request->options,
            ];

            foreach ($deviceUserPair as $deviceId => $userId) {
                CreateMessage::dispatch($payload, $deviceId, $userId);
            }
        });

        $request->delete();
    }
}
