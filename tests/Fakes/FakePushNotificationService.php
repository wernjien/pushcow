<?php

namespace Tests\Fakes;

use App\Message;
use App\Services\PushNotificationService;
use RuntimeException;

class FakePushNotificationService extends PushNotificationService
{
    /**
     * The messages that were "pushed" through this fake.
     *
     * @var Message[]
     */
    public static array $pushed = [];

    /**
     * The topics that were subscribed to through this fake, keyed by device ID.
     *
     * @var array<int, string[]>
     */
    public static array $subscriptions = [];

    /**
     * When set, push() throws instead of succeeding, simulating a delivery failure.
     */
    public static bool $shouldFail = false;

    /**
     * Reset the fake's recorded state between tests.
     */
    public static function reset(): void
    {
        static::$pushed = [];
        static::$subscriptions = [];
        static::$shouldFail = false;
    }

    protected function push(Message $message): void
    {
        if (static::$shouldFail) {
            throw new RuntimeException('Simulated push delivery failure.');
        }

        static::$pushed[] = $message;
    }

    protected function subscribeToTopic(string $topic): void
    {
        static::$subscriptions[$this->device->id][] = $topic;

        if ($this->device->topics()->where('topic', $topic)->count() === 0) {
            $this->device->topics()->create(['topic' => $topic]);
        }
    }
}
