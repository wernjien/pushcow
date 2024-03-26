<?php

namespace App\Actions;

use App\Device;
use App\Repositories\DeviceRepository;

class RegisterDevice
{
    /**
     * Update or create a device matching the attributes, and fill it with data.
     */
    public function execute(array $attributes, array $data): Device
    {
        $device = DeviceRepository::find(...$attributes);

        if (! $device instanceof Device) {
            $device = DeviceRepository::create($data);
        } else {
            DeviceRepository::update($device, $data);
        }

        return $device;
    }
}
