<?php

namespace App\Repositories;

use App\Device;
use Arr;

class DeviceRepository
{
    /**
     * Find a device by its device ID.
     *
     * @param  string  $deviceId
     * @param  string  $token
     * @param  string  $userId
     * @return \App\Device
     */
    public static function find($deviceId, $token = null, $userId = null)
    {
        return Device::withTrashed()
            ->where('device_id', $deviceId)
            ->when(! empty($token), function ($query) use ($token) {
                $query->where('token', $token);
            })
            ->when(! empty($userId), function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
    }

    /**
     * Save a new device and return the instance.
     *
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
     * Delete devices by the given data.
     *
     * @return void
     */
    public static function delete(array $data)
    {
        Device::filter($data)->delete();
    }
}
