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

use App\Http\Controllers\V3\DeviceController as DeviceControllerV3;
use App\Http\Controllers\V3\MessageController as MessageControllerV3;
use App\Http\Controllers\V3\Pulse as PulseV3;

Route::prefix('v3')->group(function () {
    Route::get('/', PulseV3::class);
    Route::post('devices', [DeviceControllerV3::class, 'store']);
    Route::delete('devices', [DeviceControllerV3::class, 'destroy']);
    Route::post('messages', [MessageControllerV3::class, 'store']);
});
