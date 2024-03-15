<?php

namespace App\Repositories;

use App\Application;
use Arr;
use Str;

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
            $application->server_key = Arr::get($data, 'server_key');
            $application->sender_id = Arr::get($data, 'sender_id');
            $application->save();
        });
    }
}
