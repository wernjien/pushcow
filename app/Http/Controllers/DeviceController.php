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
        $uniqueId = $request->input('unique_id');

        $device = DeviceRepository::find($uniqueId);

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
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device)
    {
        $device->delete();

        return Response::success();
    }
}
