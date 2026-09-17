<?php

namespace Tests\Unit;

use App\Device;
use App\Message;
use App\Services\Huawei\PushService;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class HuaweiPushServiceTest extends TestCase
{
    public function test_push_succeeds_silently_on_a_success_response(): void
    {
        $device = Device::factory()->create(['platform' => Device::PLATFORM_HUAWEI]);
        $message = Message::create([
            'application_id' => $device->application_id,
            'device_id' => $device->id,
            'notification' => '{"title":"Hi","body":"there"}',
        ]);

        $service = new class($device) extends PushService
        {
            protected function send(Message $message): object
            {
                return (object) ['code' => '80000000', 'msg' => 'Success'];
            }
        };

        $push = new ReflectionMethod($service, 'push');
        $push->setAccessible(true);
        $push->invoke($service, $message);

        $this->assertTrue(true);
    }

    public function test_push_throws_on_a_failure_response_instead_of_silently_succeeding(): void
    {
        $device = Device::factory()->create(['platform' => Device::PLATFORM_HUAWEI]);
        $message = Message::create([
            'application_id' => $device->application_id,
            'device_id' => $device->id,
            'notification' => '{"title":"Hi","body":"there"}',
        ]);

        $service = new class($device) extends PushService
        {
            protected function send(Message $message): object
            {
                return (object) ['code' => '80300007', 'msg' => 'Invalid token'];
            }
        };

        $push = new ReflectionMethod($service, 'push');
        $push->setAccessible(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid token');

        $push->invoke($service, $message);
    }
}
