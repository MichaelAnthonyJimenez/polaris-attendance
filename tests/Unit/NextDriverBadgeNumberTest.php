<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NextDriverBadgeNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_badge_is_one_when_no_drivers(): void
    {
        $this->assertSame('1', User::nextDriverBadgeNumber());
    }

    public function test_next_badge_follows_max_numeric_among_drivers_only(): void
    {
        User::factory()->create(['role' => 'driver', 'badge_number' => '3']);
        User::factory()->create(['role' => 'driver', 'badge_number' => '10']);
        User::factory()->create(['role' => 'admin', 'badge_number' => '99']);

        $this->assertSame('11', User::nextDriverBadgeNumber());
    }

    public function test_non_numeric_driver_badges_are_ignored_for_sequence(): void
    {
        User::factory()->create(['role' => 'driver', 'badge_number' => 'LEGACY']);

        $this->assertSame('1', User::nextDriverBadgeNumber());
    }
}
