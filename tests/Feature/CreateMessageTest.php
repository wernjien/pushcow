<?php

namespace Tests\Feature;

use Tests\TestCase;

class CreateMessageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create_message(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer local',
        ])->postJson('/api/v3/messages', [
            'recipients' => '*',
            'notification' => '{"title": "PushCow", "body": "Moo moo!"}',
            'data' => '',
            'options' => '',
        ]);

        $response->assertStatus(200);
    }
}
