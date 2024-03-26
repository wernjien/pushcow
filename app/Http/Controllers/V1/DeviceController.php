<?php

namespace App\Http\Controllers\V1;

use App\Actions\DeleteDevice;
use App\Actions\RegisterDevice;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DeleteDeviceRequest;
use App\Http\Requests\V1\RegisterDeviceRequest;
use App\Http\Resources\Device as DeviceResource;
use App\Support\Response;

class DeviceController extends Controller
{
    /**
     * Register or update an existing device.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterDeviceRequest $request, RegisterDevice $action)
    {
        $data = $request->validated();
        $deviceId = $request->input('device_id');

        $device = $action->execute(compact('deviceId'), $data);

        return Response::success(new DeviceResource($device));
    }

    /**
     * Unregister existing devices.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteDeviceRequest $request, DeleteDevice $action)
    {
        $action->execute($request->validated());

        return Response::success();
    }
}
