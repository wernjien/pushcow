<?php

namespace App\Console\Commands;

use App\Services\PushNotification as PushCow;
use Illuminate\Console\Command;

class Push extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new PushCow)->push();
    }
}
