<?php

namespace App\Repositories;

use Arr;
use App\Device;

class DeviceRepository
{
    /**
     * Find a device by its device ID.
     *
     * @param  string  $deviceId
     * @param  string  $token
     * @return \App\Device
     */
    public static function find($deviceId, $token = null)
    {
        return Device::withTrashed()
            ->where('device_id', $deviceId)
            ->when(! empty($token), function ($query) use ($token) {
                $query->where('token', $token);
            })
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

    /**
     * Delete devices by the given device ID and token.
     *
     * @param  string  $deviceId
     * @param  string  $token
     * @return void
     */
    public static function delete($deviceId, $token = null)
    {
        Device::where('device_id', $deviceId)
            ->when(! empty($token), function ($query) use ($token) {
                $query->where('token', $token);
            })
            ->delete();
    }
}
