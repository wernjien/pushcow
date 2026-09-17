<?php

namespace Tests\Feature;

use App\Device;
use Tests\TestCase;

class RegisterDeviceTest extends TestCase
{
    public function test_it_registers_a_new_device(): void
    {
        $application = $this->actingAsApplication();

        $response = $this->postJson('/api/v3/devices', [
            'platform' => 'ANDROID',
            'device_id' => 'd88fbe2a-4f71-4600-a196-a95b0fbe3a2f',
            'token' => 'token-1',
            'user_id' => '33',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.device.device_id', 'd88fbe2a-4f71-4600-a196-a95b0fbe3a2f')
            ->assertJsonPath('data.device.token', 'token-1')
            ->assertJsonPath('data.device.user_id', '33');

        $this->assertDatabaseHas('devices', [
            'application_id' => $application->id,
            'device_id' => 'd88fbe2a-4f71-4600-a196-a95b0fbe3a2f',
            'token' => 'token-1',
            'user_id' => '33',
        ]);
    }

    public function test_registering_the_same_device_id_again_updates_the_existing_row_instead_of_creating_a_new_one(): void
    {
        $application = $this->actingAsApplication();
        $device = Device::factory()->for($application)->create([
            'device_id' => 'same-device',
            'token' => 'old-token',
            'user_id' => '1',
        ]);

        $response = $this->postJson('/api/v3/devices', [
            'platform' => 'ANDROID',
            'device_id' => 'same-device',
            'token' => 'new-token',
            'user_id' => '1',
        ]);

        $response->assertStatus(200);

        $this->assertSame(1, Device::withTrashed()->where('device_id', 'same-device')->count());
        $this->assertSame('new-token', $device->fresh()->token);
    }

    public function test_the_user_id_binding_is_removed_when_omitted_on_update(): void
    {
        $application = $this->actingAsApplication();
        $device = Device::factory()->for($application)->create([
            'device_id' => 'same-device',
            'token' => 'a-token',
            'user_id' => '1',
        ]);

        $response = $this->postJson('/api/v3/devices', [
            'platform' => 'ANDROID',
            'device_id' => 'same-device',
            'token' => 'a-token',
        ]);

        $response->assertStatus(200);
        $this->assertNull($device->fresh()->user_id);
    }

    public function test_it_restores_a_previously_unregistered_device(): void
    {
        $application = $this->actingAsApplication();
        $device = Device::factory()->for($application)->create([
            'device_id' => 'same-device',
            'token' => 'a-token',
        ]);
        $device->delete();

        $response = $this->postJson('/api/v3/devices', [
            'platform' => 'ANDROID',
            'device_id' => 'same-device',
            'token' => 'a-token',
        ]);

        $response->assertStatus(200);
        $this->assertNotSoftDeleted($device);
    }

    public function test_it_requires_device_id_and_token(): void
    {
        $this->actingAsApplication();

        $response = $this->postJson('/api/v3/devices', [
            'platform' => 'ANDROID',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'fail')
            ->assertJsonValidationErrors(['device_id', 'token'], 'data');
    }

    public function test_it_rejects_an_unknown_platform(): void
    {
        $this->actingAsApplication();

        $response = $this->postJson('/api/v3/devices', [
            'platform' => 'WINDOWS_PHONE',
            'device_id' => 'device-1',
            'token' => 'token-1',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['platform'], 'data');
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/v3/devices', [
            'device_id' => 'device-1',
            'token' => 'token-1',
        ]);

        $response->assertStatus(401);
    }

    public function test_registering_a_device_subscribes_it_to_the_global_topic(): void
    {
        $application = $this->actingAsApplication();

        $this->postJson('/api/v3/devices', [
            'platform' => 'ANDROID',
            'device_id' => 'device-1',
            'token' => 'token-1',
        ])->assertStatus(200);

        $device = Device::where('application_id', $application->id)
            ->where('device_id', 'device-1')
            ->firstOrFail();

        $this->assertDatabaseHas('topics', [
            'device_id' => $device->id,
            'topic' => 'global',
        ]);
    }
}
