<?php

namespace App\Http\Controllers\V1;

use App\Actions\DeleteDevice;
use App\Actions\RegisterDevice;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DeleteDeviceRequest;
use App\Http\Requests\V1\RegisterDeviceRequest;
use App\Http\Resources\Device as DeviceResource;
use App\Support\Response;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    /**
     * Register or update an existing device.
     */
    public function store(RegisterDeviceRequest $request, RegisterDevice $register): JsonResponse
    {
        $data = $request->validated();
        $deviceId = $request->input('device_id');
        $attribute = compact('deviceId');

        $device = $register->execute($attribute, $data);

        return Response::success(new DeviceResource($device));
    }

    /**
     * Unregister existing devices.
     */
    public function destroy(DeleteDeviceRequest $request, DeleteDevice $delete): JsonResponse
    {
        $delete->execute($request->validated());

        return Response::success();
    }
}
