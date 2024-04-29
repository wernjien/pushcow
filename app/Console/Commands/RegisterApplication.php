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
    protected $signature = 'register:app {name} {--api-version=3} {--service-account=} {--key=} {--sender=} {--hps-client-id=} {--hps-secret=}';

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
            'api_version' => $this->option('api-version'),
            'service_account' => $this->option('service-account'),
            'server_key' => $this->option('key'),
            'sender_id' => $this->option('sender'),
            'hps_client_id' => $this->option('hps-client-id'),
            'hps_client_secret' => $this->option('hps-secret'),
        ]);

        $this->line('Application token:');
        $this->info($token);

        return 0;
    }
}
