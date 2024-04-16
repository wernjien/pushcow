<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegisterDeviceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_register_device(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer local',
        ])->postJson('/api/v3/devices', [
            'platform' => 'ANDROID',
            'device_id' => 'd88fbe2a-4f71-4600-a196-a95b0fbe3a2f',
            'token' => 'eijh0FhatOs:APA91bFfzKfVwcKIq3XFVJE0gRdJfCDYERa25NFC59M_e-RxjOoODXifdsJ3ViEjbzLVMnL4Jt-81N2uqzpT2zdizhF55Cpuleqpq6px6NAQJ3Bfe_bixRtk_AIvm9AgdycA9b9lYafU',
            'user_id' => '33',
        ]);

        $response->assertStatus(200);
    }
}
