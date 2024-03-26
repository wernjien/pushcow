<?php

namespace App\Actions;

use App\Repositories\DeviceRepository;

class DeleteDevice
{
    /**
     * Delete the devices by the given data.
     */
    public function execute(array $data): void
    {
        DeviceRepository::delete($data);
    }
}
