<?php

namespace App\Providers;

use App\Device;
use App\Services\Firebase\Legacy\CloudMessaging as FCMLegacy;
use App\Services\Firebase\V1\CloudMessaging as FCM;
use App\Services\Huawei\PushService as HuaweiPushService;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
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
        if ($this->app->environment(['local', 'dev'])) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $this->app->bind(PushNotificationService::class, function (Application $app, array $parameters = []) {
            $device = Arr::get($parameters, 'device');

            if (! $device instanceof Device) {
                $resolvable = FCM::class; // Push to topic.
            } else {
                if ($device->platform == Device::PLATFORM_HUAWEI) {
                    $resolvable = HuaweiPushService::class;
                } elseif ($device->application->shouldUseFCMLegacy()) {
                    $resolvable = FCMLegacy::class;
                } else {
                    $resolvable = FCM::class;
                }
            }

            return $app->make($resolvable, $parameters);
        });
    }
}
