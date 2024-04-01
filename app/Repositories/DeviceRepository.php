<?php

namespace App\Repositories;

use App\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class DeviceRepository
{
    /**
     * Find a device by its device ID.
     */
    public static function find(string $deviceId, ?string $token = null, ?string $userId = null): ?Device
    {
        return Device::withTrashed()
            ->where('device_id', $deviceId)
            ->when(! empty($token), function (Builder $query) use ($token) {
                $query->where('token', $token);
            })
            ->when(! empty($userId), function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
    }

    /**
     * Save a new device and return the instance.
     */
    public static function create(array $data): Device
    {
        if (! Arr::has($data, 'application_id') && auth()->check()) {
            $data['application_id'] = auth()->id();
        }

        return Device::create($data);
    }

    /**
     * Update the device in the database.
     */
    public static function update(Device &$device, array $data): bool
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
     * Delete the devices by the given data.
     */
    public static function delete(array $data): void
    {
        Device::filter($data)->delete();
    }
}
