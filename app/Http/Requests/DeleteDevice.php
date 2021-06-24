<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDevice extends FormRequest
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
        return [
            'device_id' => ['required_without_all:token,user_id', 'string'],
            'token' => ['required_without_all:device_id,user_id', 'string'],
            'user_id' => ['required_without_all:device_id,token', 'string'],
        ];
    }
}
