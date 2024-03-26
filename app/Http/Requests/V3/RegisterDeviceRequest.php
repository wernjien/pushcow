<?php

namespace App\Http\Requests\V3;

use App\Device;
use App\Http\Requests\V2\RegisterDeviceRequest as V2;
use Illuminate\Validation\Rule;

class RegisterDeviceRequest extends V2
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $platforms = Device::getPlatforms();

        return [
            'platform' => ['nullable', 'string', Rule::in($platforms)],
            'device_id' => ['required', 'string'],
            'token' => ['required', 'string'],
            'user_id' => ['nullable', 'string'],
        ];
    }
}
