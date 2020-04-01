<?php

namespace App\Repositories;

use Arr;
use App\Device;

class DeviceRepository
{
    /**
     * Find a device by its UUID.
     *
     * @param  string  $uuid
     * @return \App\Device
     */
    public static function find($uuid)
    {
        return Device::withTrashed()
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Save a new device and return the instance.
     *
     * @param  array  $data
     * @return \App\Device
     */
    public static function create(array $data)
    {
        if (! Arr::has($data, 'application_id') && auth()->check()) {
            $data['application_id'] = auth()->id();
        }

        return Device::create($data);
    }

    /**
     * Update the device in the database.
     *
     * @param  \App\Device  $device
     * @param  array  $data
     * @return bool
     */
    public static function update(Device &$device, array $data)
    {
        if ($device->trashed()) {
            $device->restore();
        }

        if (! Arr::has($data, 'user_id')) {
            $data['user_id'] = null;
        }

        return $device->fill($data)->save();
    }
}
