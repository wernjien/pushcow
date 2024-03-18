<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\V1\DeviceController as DeviceControllerV1;
use App\Http\Controllers\V1\MessageController as MessageControllerV1;
use App\Http\Controllers\V1\Pulse as PulseV1;

Route::prefix('v1')->group(function () {
    Route::get('/', PulseV1::class);
    Route::post('devices', [DeviceControllerV1::class, 'store']);
    Route::delete('devices', [DeviceControllerV1::class, 'destroy']);
    Route::post('messages', [MessageControllerV1::class, 'store']);
});
