<?php

namespace Tests\Unit;

use App\Application;
use App\Device;
use Tests\TestCase;

class DeviceSearchScopeTest extends TestCase
{
    public function test_wildcard_matches_every_device_in_scope(): void
    {
        $application = Application::factory()->create();
        Device::factory()->for($application)->count(2)->create();

        $matched = $application->devices()->search('*')->get();

        $this->assertCount(2, $matched);
    }

    public function test_it_matches_by_device_id_token_or_user_id_within_the_same_application(): void
    {
        $application = Application::factory()->create();
        $byUser = Device::factory()->for($application)->create(['user_id' => 'user-1']);
        $byToken = Device::factory()->for($application)->create(['token' => 'token-2']);
        $unrelated = Device::factory()->for($application)->create();

        $matched = $application->devices()->search('["user-1","token-2"]')->get();

        $this->assertEqualsCanonicalizing(
            [$byUser->id, $byToken->id],
            $matched->pluck('id')->all()
        );
        $this->assertFalse($matched->contains('id', $unrelated->id));
    }

    public function test_selecting_recipients_never_matches_devices_from_another_application(): void
    {
        $application = Application::factory()->create();
        Device::factory()->for($application)->create(['user_id' => 'user-1']);

        $otherApplication = Application::factory()->create();
        $otherDevice = Device::factory()->for($otherApplication)->create(['user_id' => 'user-1']);

        $matched = $application->devices()->search('["user-1"]')->get();

        $this->assertFalse($matched->contains('id', $otherDevice->id));
    }

    public function test_except_matches_unbound_and_non_excluded_devices_within_the_same_application(): void
    {
        $application = Application::factory()->create();
        $excluded = Device::factory()->for($application)->create(['user_id' => 'user-1']);
        $anonymous = Device::factory()->for($application)->create(['user_id' => null]);
        $other = Device::factory()->for($application)->create(['user_id' => 'user-2']);

        $matched = $application->devices()->search('{"except":["user-1"]}')->get();

        $this->assertEqualsCanonicalizing(
            [$anonymous->id, $other->id],
            $matched->pluck('id')->all()
        );
        $this->assertFalse($matched->contains('id', $excluded->id));
    }

    public function test_except_never_leaks_anonymous_devices_from_another_application(): void
    {
        $application = Application::factory()->create();
        Device::factory()->for($application)->create(['user_id' => 'user-1']);

        $otherApplication = Application::factory()->create();
        $otherAnonymousDevice = Device::factory()->for($otherApplication)->create(['user_id' => null]);

        $matched = $application->devices()->search('{"except":["user-1"]}')->get();

        $this->assertFalse($matched->contains('id', $otherAnonymousDevice->id));
    }
}
