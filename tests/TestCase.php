<?php

namespace Tests;

use App\Application;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Fakes\FakePushNotificationService;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /**
     * Create an application and act as it for the given API version.
     */
    protected function actingAsApplication(array $attributes = [], string $token = 'local'): Application
    {
        $application = Application::factory()->create(array_merge([
            'token' => hash('sha256', $token),
        ], $attributes));

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ]);

        $this->fakePushNotificationService();

        return $application;
    }

    /**
     * Swap the real FCM/Huawei push services for a fake that records calls
     * instead of hitting Firebase/Huawei, and reset its recorded state.
     */
    protected function fakePushNotificationService(): void
    {
        FakePushNotificationService::reset();

        $this->app->bind(PushNotificationService::class, function ($app, $parameters) {
            return new FakePushNotificationService($parameters['device'] ?? null);
        });
    }
}
