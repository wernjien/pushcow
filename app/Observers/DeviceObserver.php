<?php

namespace App\Observers;

use App\Device;
use App\Exceptions\UnauthorizedApplication;

class DeviceObserver
{
    /**
     * Handle the device retrieved event.
     *
     * @param  \App\Device  $device
     * @return void
     */
    public function retrieved(Device $device)
    {
        if ($device->application_id != auth()->id()) {
            throw new UnauthorizedApplication;
        }
    }

    /**
     * Handle the device creating event.
     *
     * @param  \App\Device  $device
     * @return void
     */
    public function creating(Device $device)
    {
        $device->application_id = auth()->id();
    }
}
