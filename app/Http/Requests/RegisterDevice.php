<?php

namespace App\Http\Requests;

use App\Device;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterDevice extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

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
        ];
    }
}
