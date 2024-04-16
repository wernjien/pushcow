<?php

namespace Tests\Feature;

use Tests\TestCase;

class PulseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_pulse(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer local',
        ])->getJson('/api/v3/');

        $response->assertStatus(200);
    }
}
