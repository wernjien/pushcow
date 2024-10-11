<?php

namespace App\Actions;

use App\Device;
use App\Repositories\DeviceRepository;
use Spatie\QueueableAction\QueueableAction;

class QueuedRegisterDevice
{
    use QueueableAction;

    /**
     * Update or create a device matching the attributes, and fill it with data.
     */
    public function execute(
        array $attributes,
        array $data,
        ?string $topic = null
    ): void {
        $device = DeviceRepository::find(...$attributes);

        if (! $device instanceof Device) {
            $device = DeviceRepository::create($data);
        } else {
            DeviceRepository::update($device, $data);
        }

        if (! empty($topic)) {
            $device->subscribe($topic);
        }
    }
}
