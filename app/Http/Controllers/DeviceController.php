<?php

namespace App\Http\Controllers;

use App\Device;
use App\Support\Response;
use App\Repositories\DeviceRepository;
use App\Http\Requests\RegisterDevice;
use App\Http\Resources\Device as DeviceResource;

class DeviceController extends Controller
{
    /**
     * Register or update an existing device.
     *
     * @param  \App\Http\Requests\RegisterDevice  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterDevice $request)
    {
        $data = $request->all();
        $deviceId = $request->input('device_id');
        $token = $request->input('token');

        $device = DeviceRepository::find($deviceId, $token);

        if (! $device instanceof Device) {
            $device = DeviceRepository::create($data);
        } else {
            DeviceRepository::update($device, $data);
        }

        return Response::success(new DeviceResource($device));
    }

    /**
     * Unregister an existing device.
     *
     * @param  string  $deviceId
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function destroy($deviceId, $token = null)
    {
        DeviceRepository::delete($deviceId, $token);

        return Response::success();
    }
}
