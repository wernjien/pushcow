<?php

namespace Database\Factories;

use App\Application;
use App\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'application_id' => Application::factory(),
            'platform' => Device::PLATFORM_ANDROID,
            'device_id' => Str::uuid()->toString(),
            'token' => Str::random(160),
            'user_id' => null,
        ];
    }
}
