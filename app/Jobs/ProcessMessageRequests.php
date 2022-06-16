<?php

namespace App\Jobs;

use DB;
use App\Message;
use App\MessageRequest;
use App\Jobs\CreateMessage;
use App\Jobs\ForwardMessages;
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
     * @param  \App\MessageRequest  $request
     * @return void
     */
    public function __construct(MessageRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $request = $this->request;
            $application = $request->application;
            $devices = $application->devices()->search($request->recipients);

            $devices->chunk(250, function ($devices) use ($request) {
                $data = $devices->pluck('user_id', 'id');

                foreach ($data as $deviceId => $userId) {
                    CreateMessage::dispatch($request, $deviceId, $userId);
                }
            });

            $request->delete();
        });

        ForwardMessages::dispatch();
    }
}
