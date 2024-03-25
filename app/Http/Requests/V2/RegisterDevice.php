<?php

namespace App\Http\Requests\V2;

use App\Device;
use App\Http\Requests\V1\RegisterDevice as RegisterDeviceV1;
use Illuminate\Validation\Rule;

class RegisterDevice extends RegisterDeviceV1
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
            'platform' => ['required', 'string', Rule::in($platforms)],
            'device_id' => ['required', 'string'],
            'token' => ['required', 'string'],
            'user_id' => ['nullable', 'string'],
        ];
    }
}
