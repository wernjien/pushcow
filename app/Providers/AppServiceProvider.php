<?php

namespace App\Providers;

use App\Device;
use App\Services\Firebase\Legacy\CloudMessaging as FCMLegacy;
use App\Services\Firebase\V1\CloudMessaging as FCM;
use App\Services\Huawei\PushService as HuaweiPushService;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $this->app->bind(PushNotificationService::class, function (Device $device) {
            if ($device->platform == Device::PLATFORM_HUAWEI) {
                return new HuaweiPushService;
            }

            if ($device->application->shouldUseFCMLegacy()) {
                return new FCMLegacy;
            }

            return new FCM;
        });
    }
}
