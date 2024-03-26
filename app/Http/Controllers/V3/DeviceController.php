<?php

namespace App\Http\Controllers\V3;

use App\Actions\DeleteDevice;
use App\Actions\RegisterDevice;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\DeleteDeviceRequest;
use App\Http\Requests\V3\RegisterDeviceRequest;
use App\Http\Resources\Device as DeviceResource;
use App\Support\Response;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    /**
     * Register or update an existing device.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterDeviceRequest $request, RegisterDevice $action): JsonResponse
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
    public function destroy(DeleteDeviceRequest $request, DeleteDevice $action): JsonResponse
    {
        $action->execute($request->validated());

        return Response::success();
    }
}
