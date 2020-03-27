<?php

namespace App\Http\Controllers;

use App\Device;
use App\Support\Response;
use App\Http\Requests\UpdateOrCreateDevice as Request;
use App\Http\Resources\Device as DeviceResource;

class DeviceController extends Controller
{
    /**
     * Register or update an existing device.
     *
     * @param  \App\Http\Requests\UpdateOrCreateDevice  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $token = $request->input('token');
        $device = Device::withTrashed()
            ->where('token', $token)
            ->first();

        if (! $device instanceof Device) {
            $device = new Device;
            $device->token = $token;
        }

        if ($device->trashed()) {
            $device->restore();
        }

        $device->user_id = $request->input('user_id');

        $device->save();

        return Response::success(new DeviceResource($device));
    }

    /**
     * Unregister an existing device.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device)
    {
        $device->delete();

        return Response::success();
    }
}
