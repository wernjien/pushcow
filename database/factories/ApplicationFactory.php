<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'token' => hash('sha256', Str::random(64)),
            'api_version' => 3,
            'service_account' => 'pushcow-dev-firebase-adminsdk-l2lfg-ae330021b4',
            'server_key' => null,
            'sender_id' => null,
            'hps_client_id' => null,
            'hps_client_secret' => null,
        ];
    }
}
