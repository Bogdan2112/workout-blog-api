<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\GymOpeningHour;
use App\Models\GymPhoto;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MembershipApiTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('protectedPaths')]
    public function test_catalog_and_memberships_return_401_for_guests(string $path): void
    {
        $this->getJson($path)->assertUnauthorized();
    }

    /** @return array<string, array{string}> */
    public static function protectedPaths(): array
    {
        return [
            'gym detail' => ['/api/v1/gyms/1'],
            'plans' => ['/api/v1/gyms/1/membership-plans'],
            'plan detail' => ['/api/v1/gyms/1/membership-plans/1'],
            'memberships' => ['/api/v1/memberships'],
            'membership detail' => ['/api/v1/memberships/1'],
        ];
    }

    public function test_gym_details_include_description_ordered_photos_and_weekly_hours(): void
    {
        $user = User::factory()->create();
        $gym = Gym::factory()->create(['description' => 'Sala pentru demonstrație.']);
        GymPhoto::factory()->for($gym)->create(['sort_order' => 2]);
        $firstPhoto = GymPhoto::factory()->for($gym)->create(['sort_order' => 0]);
        GymOpeningHour::factory()->for($gym)->create(['day_of_week' => 7, 'opens_at' => null, 'closes_at' => null]);
        GymOpeningHour::factory()->for($gym)->create(['day_of_week' => 1]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('gyms.show', $gym));

        $response->assertOk()->assertJsonPath('data.description', 'Sala pentru demonstrație.')
            ->assertJsonPath('data.photos.0.id', $firstPhoto->id)
            ->assertJsonPath('data.opening_hours.0.day_of_week', 1)
            ->assertJsonPath('data.opening_hours.1.is_closed', true);
        $this->assertStringEndsWith('/storage/gyms/demo/fitness.jpg', $response->json('data.photos.0.url'));
    }

    public function test_plan_listing_excludes_other_gyms_and_inactive_plans_and_paginates(): void
    {
        $gym = Gym::factory()->create();
        MembershipPlan::factory()->for($gym)->create(['duration_days' => 90]);
        $shortPlan = MembershipPlan::factory()->for($gym)->create(['duration_days' => 30]);
        MembershipPlan::factory()->inactive()->for($gym)->create();
        MembershipPlan::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('membership-plans.index', ['gym' => $gym, 'per_page' => 1]))
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $shortPlan->id)
            ->assertJsonPath('data.0.price_amount', 60000)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_plan_from_another_gym_returns_404(): void
    {
        $gym = Gym::factory()->create();
        $otherPlan = MembershipPlan::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('membership-plans.show', ['gym' => $gym, 'membershipPlan' => $otherPlan]))
            ->assertNotFound();
    }

    public function test_inactive_plan_returns_404(): void
    {
        $plan = MembershipPlan::factory()->inactive()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('membership-plans.show', ['gym' => $plan->gym, 'membershipPlan' => $plan]))
            ->assertNotFound();
    }

    public function test_inactive_gym_returns_404(): void
    {
        $gym = Gym::factory()->create(['is_active' => false]);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('gyms.show', $gym))->assertNotFound();
    }

    public function test_user_only_sees_their_own_memberships(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $membership = Membership::factory()->for($user)->create();
        Membership::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson(route('memberships.index', ['user_id' => 999]))
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $membership->id)
            ->assertJsonPath('data.0.status', 'active');
    }

    public function test_user_can_view_their_membership_after_plan_is_disabled(): void
    {
        $this->freezeTime();
        $membership = Membership::factory()->create();
        $membership->membershipPlan->update(['is_active' => false]);
        Sanctum::actingAs($membership->user);

        $this->getJson(route('memberships.show', $membership))
            ->assertOk()->assertJsonPath('data.id', $membership->id);
    }

    public function test_another_users_membership_returns_404(): void
    {
        $membership = Membership::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('memberships.show', $membership))->assertNotFound();
    }

    public function test_membership_policy_only_allows_the_member_to_view_it(): void
    {
        $membership = Membership::factory()->create();
        $otherUser = User::factory()->create();

        $this->assertTrue(Gate::forUser($membership->user)->allows('view', $membership));
        $this->assertFalse(Gate::forUser($otherUser)->allows('view', $membership));
    }

    public function test_invalid_pagination_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('memberships.index', ['page' => 0, 'per_page' => 101]))
            ->assertUnprocessable()->assertJsonValidationErrors(['page', 'per_page']);
    }

    public function test_memberships_cannot_be_created_through_the_api(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/memberships', ['is_demo' => true])->assertMethodNotAllowed();
        $this->assertDatabaseEmpty('memberships');
    }
}
