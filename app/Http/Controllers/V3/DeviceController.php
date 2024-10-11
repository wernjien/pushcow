<?php

namespace App\Http\Controllers\V3;

use App\Actions\DeleteDevice;
use App\Actions\QueuedRegisterDevice;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\DeleteDeviceRequest;
use App\Http\Requests\V3\RegisterDeviceRequest;
use App\Support\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class DeviceController extends Controller
{
    /**
     * Register or update an existing device.
     */
    public function store(RegisterDeviceRequest $request, QueuedRegisterDevice $register): JsonResponse
    {
        $data = $request->validated();
        $deviceId = $request->input('device_id');
        $attribute = compact('deviceId');

        if (! Arr::has($data, 'application_id') && auth()->check()) {
            $data['application_id'] = auth()->id();
        }

        $register->onQueue()->execute($attribute, $data, 'global');

        return Response::success();
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
