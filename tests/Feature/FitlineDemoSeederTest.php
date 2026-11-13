<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Membership;
use App\Models\MembershipPlan;
use Database\Seeders\FitlineDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FitlineDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_is_repeatable_without_duplicating_or_resetting_existing_data(): void
    {
        $this->freezeTime();
        $unrelatedGym = Gym::factory()->create();
        $this->seed(FitlineDemoSeeder::class);
        $demoGym = Gym::query()->whereKeyNot($unrelatedGym->id)->firstOrFail();
        $demoGym->openingHours()->where('day_of_week', 7)->update(['opens_at' => null, 'closes_at' => null]);
        $membership = Membership::firstOrFail();
        $initialExpiry = $membership->ends_at->toISOString();
        $this->travel(1)->days();

        $this->seed(FitlineDemoSeeder::class);

        $this->assertDatabaseCount('gyms', 3);
        $this->assertDatabaseCount('gym_opening_hours', 14);
        $this->assertDatabaseCount('membership_plans', 24);
        $this->assertDatabaseCount('memberships', 1);
        $this->assertSame(0, $unrelatedGym->openingHours()->count());
        $this->assertNull($demoGym->openingHours()->where('day_of_week', 7)->firstOrFail()->opens_at);
        $this->assertSame($initialExpiry, $membership->fresh()->ends_at->toISOString());
        $this->assertSame([30, 90, 180, 360], MembershipPlan::query()->distinct()->orderBy('duration_days')->pluck('duration_days')->all());
        $partTime = MembershipPlan::query()->where('name', 'Part Time — 1 lună')->firstOrFail();
        $this->assertSame('17:00', substr($partTime->access_ends_at, 0, 5));
    }
}
