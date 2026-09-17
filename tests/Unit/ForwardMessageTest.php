<?php

namespace Tests\Unit;

use App\Application;
use App\Device;
use App\Jobs\ForwardMessage;
use App\Message;
use Tests\Fakes\FakePushNotificationService;
use Tests\TestCase;

class ForwardMessageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakePushNotificationService();
    }

    public function test_a_successful_push_marks_the_message_as_sent(): void
    {
        $device = Device::factory()->create();
        $message = $this->createMessageFor($device);

        (new ForwardMessage($message))->handle();

        $this->assertSame(Message::STATUS_SUCCESS, $message->fresh()->status);
        $this->assertCount(1, FakePushNotificationService::$pushed);
    }

    public function test_a_failed_push_marks_the_message_as_failed_and_is_not_overwritten_to_success(): void
    {
        FakePushNotificationService::$shouldFail = true;

        $device = Device::factory()->create();
        $message = $this->createMessageFor($device);

        (new ForwardMessage($message))->handle();

        $this->assertSame(Message::STATUS_FAILED, $message->fresh()->status);
    }

    public function test_it_resolves_the_service_for_a_topic_message_without_a_device(): void
    {
        $application = Application::factory()->create();
        $message = Message::create([
            'application_id' => $application->id,
            'device_id' => null,
            'topic' => 'global',
            'notification' => '{"title":"Hi","body":"there"}',
        ]);

        (new ForwardMessage($message))->handle();

        $this->assertSame(Message::STATUS_SUCCESS, $message->fresh()->status);
    }

    protected function createMessageFor(Device $device): Message
    {
        return Message::create([
            'application_id' => $device->application_id,
            'device_id' => $device->id,
            'notification' => '{"title":"Hi","body":"there"}',
        ]);
    }
}
