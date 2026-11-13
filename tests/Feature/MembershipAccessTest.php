<?php

namespace Tests\Feature;

use App\Models\GymOpeningHour;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Services\MembershipAccessService;
use App\Services\MembershipService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MembershipAccessTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('accessCases')]
    public function test_access_respects_the_plan_and_local_opening_hours(string $type, string $localTime, bool $allowed): void
    {
        $membership = $this->membership($type);
        $moment = CarbonImmutable::parse($localTime, 'Europe/Chisinau');

        $result = app(MembershipAccessService::class)->allowsAccessAt($membership->user, $membership, $moment);

        $this->assertSame($allowed, $result);
    }

    /** @return array<string, array{string, string, bool}> */
    public static function accessCases(): array
    {
        return [
            'part time before opening' => ['partTime', '2026-11-12 06:59:59', false],
            'part time at opening' => ['partTime', '2026-11-12 07:00:00', true],
            'part time before cutoff' => ['partTime', '2026-11-12 16:59:59', true],
            'part time at cutoff' => ['partTime', '2026-11-12 17:00:00', false],
            'part time on weekend' => ['partTime', '2026-11-14 10:00:00', false],
            'full time weekday evening' => ['fullTime', '2026-11-12 21:00:00', true],
            'full time at closing' => ['fullTime', '2026-11-12 22:00:00', false],
            'weekend weekday refused' => ['weekend', '2026-11-12 10:00:00', false],
            'weekend before opening' => ['weekend', '2026-11-14 08:59:59', false],
            'weekend at opening' => ['weekend', '2026-11-14 09:00:00', true],
            'sunday before closing' => ['weekend', '2026-11-15 17:59:59', true],
            'sunday at closing' => ['weekend', '2026-11-15 18:00:00', false],
        ];
    }

    public function test_access_ends_at_the_earlier_gym_closing_time(): void
    {
        $membership = $this->membership('partTime');
        $membership->membershipPlan->gym->openingHours()->where('day_of_week', 4)->update(['closes_at' => '16:00']);
        $moment = CarbonImmutable::parse('2026-11-12 15:00', 'Europe/Chisinau');

        $endsAt = app(MembershipAccessService::class)->accessEndsAt($membership->user, $membership, $moment);

        $this->assertSame('2026-11-12 14:00:00', $endsAt?->format('Y-m-d H:i:s'));
    }

    public function test_closed_or_missing_opening_hours_refuse_access(): void
    {
        $membership = $this->membership('fullTime');
        $membership->membershipPlan->gym->openingHours()->where('day_of_week', 4)->update(['opens_at' => null, 'closes_at' => null]);
        $service = app(MembershipAccessService::class);
        $moment = CarbonImmutable::parse('2026-11-12 10:00', 'Europe/Chisinau');

        $this->assertFalse($service->allowsAccessAt($membership->user, $membership, $moment));
        $membership->membershipPlan->gym->openingHours()->delete();
        $this->assertFalse($service->allowsAccessAt($membership->user, $membership, $moment));
    }

    public function test_expiry_cancellation_and_ownership_are_enforced(): void
    {
        $membership = $this->membership('fullTime');
        $moment = CarbonImmutable::parse('2026-11-12 10:00', 'Europe/Chisinau');
        $service = app(MembershipAccessService::class);

        $this->assertFalse($service->allowsAccessAt(User::factory()->create(), $membership, $moment));
        $membership->ends_at = $moment->utc();
        $this->assertFalse($service->allowsAccessAt($membership->user, $membership, $moment));
        $membership->ends_at = $moment->utc()->addDay();
        $membership->starts_at = $moment->utc()->addHour();
        $this->assertFalse($service->allowsAccessAt($membership->user, $membership, $moment));
        $membership->starts_at = $moment->utc()->subDay();
        $membership->cancelled_at = $moment->utc();
        $this->assertFalse($service->allowsAccessAt($membership->user, $membership, $moment));
    }

    public function test_disabled_gym_refuses_access_but_disabled_offer_does_not_cancel_existing_access(): void
    {
        $membership = $this->membership('fullTime');
        $moment = CarbonImmutable::parse('2026-11-12 10:00', 'Europe/Chisinau');
        $service = app(MembershipAccessService::class);
        $membership->membershipPlan->update(['is_active' => false]);

        $this->assertTrue($service->allowsAccessAt($membership->user, $membership, $moment));
        $membership->membershipPlan->gym->update(['is_active' => false]);
        $this->assertFalse($service->allowsAccessAt($membership->user, $membership, $moment));
    }

    public function test_plan_changes_do_not_rewrite_existing_membership_conditions(): void
    {
        $membership = $this->membership('partTime');
        $membership->membershipPlan->update([
            'name' => 'Changed', 'allowed_days' => [6, 7], 'access_ends_at' => '12:00', 'duration_days' => 90, 'price_amount' => 99999,
        ]);
        $membership->refresh();
        $moment = CarbonImmutable::parse('2026-11-12 16:00', 'Europe/Chisinau');

        $this->assertTrue(app(MembershipAccessService::class)->allowsAccessAt($membership->user, $membership, $moment));
        $this->assertSame(30, $membership->duration_days);
        $this->assertSame(60000, $membership->price_amount);
        $this->assertSame('Part Time — 1 lună', $membership->plan_name);
    }

    public function test_entire_interval_must_fit_before_access_expiry(): void
    {
        $membership = $this->membership('partTime');
        $service = app(MembershipAccessService::class);
        $start = CarbonImmutable::parse('2026-11-12 16:00', 'Europe/Chisinau');

        $this->assertTrue($service->allowsInterval($membership->user, $membership, $start, $start->addHour()));
        $this->assertFalse($service->allowsInterval($membership->user, $membership, $start, $start->addMinutes(61)));
        $this->assertFalse($service->allowsInterval($membership->user, $membership, $start, $start));
        $membership->ends_at = $start->utc()->addMinutes(30);
        $this->assertFalse($service->allowsInterval($membership->user, $membership, $start, $start->addHour()));
    }

    #[DataProvider('durations')]
    public function test_membership_duration_uses_fixed_days(int $days, string $expectedEnd): void
    {
        $plan = MembershipPlan::factory()->create(['duration_days' => $days]);
        $user = User::factory()->create();

        $membership = app(MembershipService::class)->createDemoMembership(
            $user, $plan, CarbonImmutable::parse('2026-01-01 10:00:00', 'UTC'),
        );

        $this->assertSame($expectedEnd, $membership->ends_at->format('Y-m-d H:i:s'));
        $this->assertTrue($membership->is_demo);
        $this->assertDatabaseHas('memberships', ['id' => $membership->id, 'duration_days' => $days]);
    }

    /** @return array<string, array{int, string}> */
    public static function durations(): array
    {
        return [
            'one month' => [30, '2026-01-31 10:00:00'],
            'three months' => [90, '2026-04-01 10:00:00'],
            'six months' => [180, '2026-06-30 10:00:00'],
            'twelve months' => [360, '2026-12-27 10:00:00'],
        ];
    }

    private function membership(string $type): Membership
    {
        $factory = MembershipPlan::factory();
        $plan = match ($type) {
            'partTime' => $factory->partTime()->create(),
            'weekend' => $factory->weekend()->create(),
            default => $factory->create(),
        };

        foreach (range(1, 7) as $day) {
            GymOpeningHour::factory()->for($plan->gym)->create([
                'day_of_week' => $day,
                'opens_at' => $day <= 5 ? '07:00' : '09:00',
                'closes_at' => $day <= 5 ? '22:00' : ($day === 6 ? '20:00' : '18:00'),
            ]);
        }

        return app(MembershipService::class)->createDemoMembership(
            User::factory()->create(), $plan, CarbonImmutable::parse('2026-11-01 00:00', 'UTC'),
        );
    }
}
