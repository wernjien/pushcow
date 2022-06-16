<?php

namespace App\Jobs;

use App\Message;
use App\Services\PushNotification as PushCow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ForwardMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The notification message.
     *
     * @var \App\Message
     */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param  \App\Message  $message
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->onQueue('forward-message');

        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new PushCow)->push($this->message);
    }
}
