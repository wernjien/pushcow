<?php

namespace Tests\Feature;

use App\Application;
use App\Device;
use App\Message;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PulseTest extends TestCase
{
    public function test_it_reports_the_application_name(): void
    {
        $this->actingAsApplication(['name' => 'My App']);

        $response = $this->getJson('/api/v3/');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'My App')
            ->assertJsonPath('data.last_received_at', null)
            ->assertJsonPath('data.last_pushed_at', null);
    }

    public function test_it_reports_its_own_last_received_and_pushed_timestamps(): void
    {
        $application = $this->actingAsApplication();
        $device = Device::factory()->for($application)->create();

        $message = Message::create([
            'application_id' => $application->id,
            'device_id' => $device->id,
            'notification' => '{"title":"Hi","body":"there"}',
        ]);
        $message->status = Message::STATUS_SUCCESS;
        $message->save();

        $response = $this->getJson('/api/v3/');

        $response->assertStatus(200)
            ->assertJsonPath('data.last_received_at', $message->created_at->format('Y-m-d H:i:s'))
            ->assertJsonPath('data.last_pushed_at', $message->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_it_does_not_leak_activity_from_other_applications(): void
    {
        $application = $this->actingAsApplication();
        $device = Device::factory()->for($application)->create();

        Carbon::setTestNow(now()->subHour());
        $ownMessage = Message::create([
            'application_id' => $application->id,
            'device_id' => $device->id,
            'notification' => '{"title":"Hi","body":"there"}',
        ]);
        $ownMessage->status = Message::STATUS_SUCCESS;
        $ownMessage->save();
        Carbon::setTestNow();

        // A later message on a different application must never surface here.
        $otherApplicationDevice = Device::factory()->create();
        $otherMessage = Message::create([
            'application_id' => $otherApplicationDevice->application_id,
            'device_id' => $otherApplicationDevice->id,
            'notification' => '{"title":"Hi","body":"there"}',
        ]);
        $otherMessage->status = Message::STATUS_SUCCESS;
        $otherMessage->save();

        $this->assertTrue($otherMessage->created_at->greaterThan($ownMessage->created_at));

        $response = $this->getJson('/api/v3/');

        $response->assertStatus(200)
            ->assertJsonPath('data.last_received_at', $ownMessage->created_at->format('Y-m-d H:i:s'))
            ->assertJsonPath('data.last_pushed_at', $ownMessage->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_topic_broadcasts_from_other_applications_never_leak_into_pulse(): void
    {
        $application = $this->actingAsApplication();

        Carbon::setTestNow(now()->subHour());
        $ownMessage = Message::create([
            'application_id' => $application->id,
            'device_id' => null,
            'topic' => 'global',
            'notification' => '{"title":"Hi","body":"there"}',
        ]);
        $ownMessage->status = Message::STATUS_SUCCESS;
        $ownMessage->save();
        Carbon::setTestNow();

        // A later topic broadcast on a different application must never surface here.
        $otherApplication = Application::factory()->create();
        $otherMessage = Message::create([
            'application_id' => $otherApplication->id,
            'device_id' => null,
            'topic' => 'global',
            'notification' => '{"title":"Hi","body":"there"}',
        ]);
        $otherMessage->status = Message::STATUS_SUCCESS;
        $otherMessage->save();

        $this->assertTrue($otherMessage->created_at->greaterThan($ownMessage->created_at));

        $response = $this->getJson('/api/v3/');

        $response->assertStatus(200)
            ->assertJsonPath('data.last_received_at', $ownMessage->created_at->format('Y-m-d H:i:s'))
            ->assertJsonPath('data.last_pushed_at', $ownMessage->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_it_counts_topic_broadcasts_towards_last_activity(): void
    {
        $application = $this->actingAsApplication();

        $message = Message::create([
            'application_id' => $application->id,
            'device_id' => null,
            'topic' => 'global',
            'notification' => '{"title":"Hi","body":"there"}',
        ]);
        $message->status = Message::STATUS_SUCCESS;
        $message->save();

        $response = $this->getJson('/api/v3/');

        $response->assertStatus(200)
            ->assertJsonPath('data.last_received_at', $message->created_at->format('Y-m-d H:i:s'))
            ->assertJsonPath('data.last_pushed_at', $message->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->getJson('/api/v3/');

        $response->assertStatus(401);
    }
}
