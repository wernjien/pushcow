<?php

namespace App\Jobs;

use DB;
use App\Message;
use App\MessageRequest;
use App\Jobs\SendPushNotification;
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
                $now = now();
                $data = [];

                foreach ($devices as $device) {
                    $data[] = [
                        'device_id' => $device->id,
                        'notification' => $request->notification,
                        'data' => $this->prependUserId($request->data, $device),
                        'options' => $request->options,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Message::insert($data);
            });

            $request->delete();
        });

        SendPushNotification::dispatch();
    }

    /**
     * Prepend user ID to data.
     *
     * @param  string  $data
     * @param  \App\Device  $device
     * @return string
     */
    protected function prependUserId($data, $device)
    {
        $userId = data_get($device, 'user_id');

        if (empty($userId)) {
            return $data;
        }

        $data = array_merge(['_user_id' => $userId], json_decode($data, true));

        return json_encode($data);
    }
}
