<?php

namespace Tests\Feature;

use App\Device;
use Tests\TestCase;

class UnregisterDeviceTest extends TestCase
{
    public function test_it_unregisters_a_device_by_device_id(): void
    {
        $application = $this->actingAsApplication();
        $device = Device::factory()->for($application)->create();

        $response = $this->deleteJson('/api/v3/devices', [
            'device_id' => $device->device_id,
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertSoftDeleted($device);
    }

    public function test_it_unregisters_devices_by_user_id(): void
    {
        $application = $this->actingAsApplication();
        $device = Device::factory()->for($application)->create(['user_id' => '33']);

        $response = $this->deleteJson('/api/v3/devices', [
            'user_id' => '33',
        ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted($device);
    }

    public function test_it_requires_at_least_one_identifier(): void
    {
        $this->actingAsApplication();

        $response = $this->deleteJson('/api/v3/devices', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_id', 'token', 'user_id'], 'data');
    }

    public function test_it_does_not_unregister_devices_belonging_to_another_application(): void
    {
        $this->actingAsApplication();
        $otherApplicationDevice = Device::factory()->create(['user_id' => '33']);

        $this->deleteJson('/api/v3/devices', ['user_id' => '33'])
            ->assertStatus(200);

        $this->assertNotSoftDeleted($otherApplicationDevice);
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/v3/devices', ['device_id' => 'device-1']);

        $response->assertStatus(401);
    }
}
