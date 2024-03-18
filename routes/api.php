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
use App\Http\Controllers\V2\DeviceController as DeviceControllerV2;
use App\Http\Controllers\V2\MessageController as MessageControllerV2;
use App\Http\Controllers\V2\Pulse as PulseV2;
use App\Http\Controllers\V3\DeviceController as DeviceControllerV3;
use App\Http\Controllers\V3\MessageController as MessageControllerV3;
use App\Http\Controllers\V3\Pulse as PulseV3;

Route::prefix('v1')->group(function () {
    Route::get('/', PulseV1::class);
    Route::post('devices', [DeviceControllerV1::class, 'store']);
    Route::delete('devices', [DeviceControllerV1::class, 'destroy']);
    Route::post('messages', [MessageControllerV1::class, 'store']);
});

Route::prefix('v2')->group(function () {
    Route::get('/', PulseV2::class);
    Route::post('devices', [DeviceControllerV2::class, 'store']);
    Route::delete('devices', [DeviceControllerV2::class, 'destroy']);
    Route::post('messages', [MessageControllerV2::class, 'store']);
});

Route::prefix('v3')->group(function () {
    Route::get('/', PulseV3::class);
    Route::post('devices', [DeviceControllerV3::class, 'store']);
    Route::delete('devices', [DeviceControllerV3::class, 'destroy']);
    Route::post('messages', [MessageControllerV3::class, 'store']);
});
