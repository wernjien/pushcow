<?php

namespace App\Http\Controllers\V1;

use App\Device;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DeleteDevice;
use App\Http\Requests\V1\RegisterDevice;
use App\Http\Resources\Device as DeviceResource;
use App\Repositories\DeviceRepository;
use App\Support\Response;

class DeviceController extends Controller
{
    /**
     * Register or update an existing device.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterDevice $request)
    {
        $data = $request->validated();
        $deviceId = $request->input('device_id');

        $device = DeviceRepository::find($deviceId);

        if (! $device instanceof Device) {
            $device = DeviceRepository::create($data);
        } else {
            DeviceRepository::update($device, $data);
        }

        return Response::success(new DeviceResource($device));
    }

    /**
     * Unregister existing devices.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteDevice $request)
    {
        DeviceRepository::delete($request->validated());

        return Response::success();
    }
}
