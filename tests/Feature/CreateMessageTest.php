<?php

namespace Tests\Feature;

use App\Jobs\ProcessMessageRequests;
use App\MessageRequest;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateMessageTest extends TestCase
{
    public function test_it_accepts_a_wildcard_recipient(): void
    {
        $application = $this->actingAsApplication();
        Queue::fake();

        $response = $this->postJson('/api/v3/messages', [
            'recipients' => '*',
            'notification' => '{"title": "PushCow", "body": "Moo moo!"}',
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('message_requests', [
            'application_id' => $application->id,
            'recipients' => '*',
        ]);
        Queue::assertPushed(ProcessMessageRequests::class);
    }

    public function test_it_accepts_an_array_of_recipients_and_stores_it_searchably(): void
    {
        $this->actingAsApplication();
        Queue::fake();

        $response = $this->postJson('/api/v3/messages', [
            'recipients' => ['user-1', 'user-2'],
            'notification' => '{"title": "PushCow", "body": "Moo moo!"}',
        ]);

        $response->assertStatus(200);

        $messageRequest = MessageRequest::firstOrFail();
        $this->assertIsString($messageRequest->recipients);
        $this->assertSame(['user-1', 'user-2'], json_decode($messageRequest->recipients, true));
    }

    public function test_it_requires_a_notification(): void
    {
        $this->actingAsApplication();

        $response = $this->postJson('/api/v3/messages', [
            'recipients' => '*',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['notification'], 'data');
    }

    public function test_it_rejects_malformed_json_fields(): void
    {
        $this->actingAsApplication();

        $response = $this->postJson('/api/v3/messages', [
            'recipients' => '*',
            'notification' => 'not-json',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['notification'], 'data');
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/v3/messages', [
            'recipients' => '*',
            'notification' => '{"title": "PushCow", "body": "Moo moo!"}',
        ]);

        $response->assertStatus(401);
    }
}
