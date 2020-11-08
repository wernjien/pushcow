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

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return App\Support\Response::success();
    });

    Route::post('devices', 'DeviceController@store');
    Route::delete('devices', 'DeviceController@destroy');

    Route::post('messages', 'MessageController@store');
});
