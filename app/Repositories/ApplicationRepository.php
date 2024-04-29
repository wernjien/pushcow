<?php

namespace App\Repositories;

use App\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ApplicationRepository
{
    /**
     * Create a new application and return the plaintext token.
     *
     * @return \App\Application
     */
    public static function createGetToken(array $data)
    {
        return tap(Str::random(64), function ($token) use ($data) {
            $application = new Application;
            $application->name = Arr::get($data, 'name');
            $application->token = hash('sha256', $token);
            $application->api_version = Arr::get($data, 'api_version');
            $application->service_account = Arr::get($data, 'service_account');
            $application->server_key = Arr::get($data, 'server_key');
            $application->sender_id = Arr::get($data, 'sender_id');
            $application->hps_client_id = Arr::get($data, 'hps_client_id');
            $application->hps_client_secret = Arr::get($data, 'hps_client_secret');
            $application->save();
        });
    }
}
