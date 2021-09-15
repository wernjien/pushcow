<?php

namespace App\Console\Commands;

use App\Repositories\ApplicationRepository;
use Illuminate\Console\Command;

class RegisterApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register:app {name} {--key=} {--sender=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a new application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = ApplicationRepository::createGetToken([
            'name' => $this->argument('name'),
            'server_key' => $this->option('key'),
            'sender_id' => $this->option('sender'),
        ]);

        $this->line('Application token:');
        $this->info($token);

        return 0;
    }
}
