<?php

namespace Tests\Feature;

use Tests\TestCase;

class UnregisterDeviceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_unregister_device(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer local',
        ])->deleteJson('/api/v3/devices', [
            // 'device_id' => '',
            // 'token' => '',
            'user_id' => '33',
        ]);

        $response->assertStatus(200);
    }
}
