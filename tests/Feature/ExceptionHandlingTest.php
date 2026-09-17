<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    public function test_an_unknown_route_returns_a_404(): void
    {
        $this->actingAsApplication();

        $response = $this->getJson('/api/v3/does-not-exist');

        $response->assertStatus(404)->assertJsonPath('status', 'error');
    }

    public function test_an_unsupported_method_returns_a_405(): void
    {
        $this->actingAsApplication();

        $response = $this->getJson('/api/v3/devices');

        $response->assertStatus(405)->assertJsonPath('status', 'error');
    }

    public function test_a_missing_bearer_token_returns_a_401_not_a_generic_400(): void
    {
        $response = $this->getJson('/api/v3/');

        $response->assertStatus(401)->assertJsonPath('status', 'error');
    }

    public function test_an_invalid_bearer_token_returns_a_401(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer not-a-real-token'])
            ->getJson('/api/v3/');

        $response->assertStatus(401)->assertJsonPath('status', 'error');
    }

    public function test_a_validation_failure_returns_422_with_field_level_errors(): void
    {
        $this->actingAsApplication();

        $response = $this->postJson('/api/v3/devices', []);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'fail')
            ->assertJsonValidationErrors(['device_id', 'token'], 'data');
    }
}
