<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Gym;
use App\Models\GymOpeningHour;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_book_a_session_with_a_valid_membership(): void
    {
        $now = CarbonImmutable::parse('2026-11-30 10:00:00', 'UTC');
        $this->travelTo($now);

        $user = User::factory()->create();
        $gym = Gym::factory()->create(['timezone' => 'Europe/Chisinau']);
        $plan = MembershipPlan::factory()->for($gym)->create();
        $membership = Membership::factory()->for($user)->forPlan($plan, $now)->create();

        $session = TrainingSession::factory()->for($gym)->create([
            'starts_at' => '2026-12-01 16:00:00',
            'ends_at' => '2026-12-01 17:00:00',
        ]);

        GymOpeningHour::factory()->for($gym)->create([
            'day_of_week' => 2,
            'opens_at' => '07:00',
            'closes_at' => '22:00',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('bookings.store', [
            'gym' => $gym,
            'trainingSession' => $session,
        ]), [
            'membership_id' => $membership->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.training_session_id', $session->id);

        $this->assertDatabaseHas('bookings', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'training_session_id' => $session->id,
        ]);
    }

    public function test_member_cannot_book_the_same_session_twice(): void
    {
        $now = CarbonImmutable::parse('2026-11-30 10:00:00', 'UTC');
        $this->travelTo($now);

        $user = User::factory()->create();
        $gym = Gym::factory()->create(['timezone' => 'Europe/Chisinau']);
        $plan = MembershipPlan::factory()->for($gym)->create();
        $membership = Membership::factory()->for($user)->forPlan($plan, $now)->create();

        $session = TrainingSession::factory()->for($gym)->create([
            'starts_at' => '2026-12-01 16:00:00',
            'ends_at' => '2026-12-01 17:00:00',
        ]);

        GymOpeningHour::factory()->for($gym)->create([
            'day_of_week' => 2,
            'opens_at' => '07:00',
            'closes_at' => '22:00',
        ]);

        Sanctum::actingAs($user);

        $url = route('bookings.store', [
            'gym' => $gym,
            'trainingSession' => $session,
        ]);

        $payload = ['membership_id' => $membership->id];

        $this->postJson($url, $payload)->assertCreated();

        $this->postJson($url, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('training_session');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_guest_cannot_book_a_session(): void
    {
        $session = TrainingSession::factory()->create();

        $this->postJson(route('bookings.store', [
            'gym' => $session->gym_id,
            'trainingSession' => $session->id,
        ]), [
            'membership_id' => 1,
        ])->assertUnauthorized();

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_member_cannot_book_using_another_users_membership(): void
    {
        $now = CarbonImmutable::parse('2026-11-30 10:00:00', 'UTC');
        $this->travelTo($now);

        $session = TrainingSession::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'gym_id' => $session->gym_id,
        ]);

        $owner = User::factory()->create();
        $membership = Membership::factory()
            ->for($owner)
            ->forPlan($plan, $now)
            ->create();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('bookings.store', [
            'gym' => $session->gym_id,
            'trainingSession' => $session->id,
        ]), [
            'membership_id' => $membership->id,
        ])->assertNotFound();

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_membership_for_another_gym_returns_422(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $otherPlan = MembershipPlan::factory()->create();
        $membership->membershipPlan()->associate($otherPlan);
        $membership->save();

        $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('membership_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    #[DataProvider('unavailableSessions')]
    public function test_unavailable_session_returns_422(array $attributes): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $session->update($attributes);

        $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('training_session');

        $this->assertDatabaseCount('bookings', 0);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function unavailableSessions(): array
    {
        return [
            'cancelled' => [['cancelled_at' => '2026-11-30 09:00:00']],
            'starts now' => [['starts_at' => '2026-11-30 10:00:00', 'ends_at' => '2026-11-30 11:00:00']],
            'already started' => [['starts_at' => '2026-11-30 09:00:00', 'ends_at' => '2026-11-30 11:00:00']],
        ];
    }

    #[DataProvider('ineligibleMemberships')]
    public function test_ineligible_membership_returns_422(array $attributes): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $membership->update($attributes);

        $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('membership_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function ineligibleMemberships(): array
    {
        return [
            'expired at session start' => [['ends_at' => '2026-12-01 16:00:00']],
            'expires during session' => [['ends_at' => '2026-12-01 16:30:00']],
            'starts after session' => [['starts_at' => '2026-12-02 10:00:00']],
            'cancelled' => [['cancelled_at' => '2026-11-30 09:00:00']],
            'wrong weekday' => [['allowed_days' => [6, 7]]],
            'ends after local cutoff' => [['access_ends_at' => '18:30']],
        ];
    }

    public function test_full_session_returns_422(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $session->update(['capacity' => 1]);
        $otherUser = User::factory()->create();
        Booking::factory()->for($session, 'trainingSession')->for($otherUser)->create();

        $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('training_session');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseMissing('bookings', ['user_id' => $user->id]);
    }

    public function test_session_with_unlimited_capacity_accepts_another_booking(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $session->update(['capacity' => null]);
        Booking::factory()->for($session, 'trainingSession')->for(User::factory())->create();

        $response = $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseHas('bookings', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'training_session_id' => $session->id,
        ]);
    }

    public function test_session_under_another_gym_returns_404(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $otherGym = Gym::factory()->create();

        $this->postJson(route('bookings.store', ['gym' => $otherGym, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ])->assertNotFound();

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_inactive_gym_returns_404(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $session->gym->update(['is_active' => false]);

        $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ])->assertNotFound();

        $this->assertDatabaseCount('bookings', 0);
    }

    #[DataProvider('invalidBookingPayloads')]
    public function test_invalid_membership_id_returns_422(array $payload): void
    {
        [$user, $session] = $this->bookableSession();

        $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('membership_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidBookingPayloads(): array
    {
        return [
            'missing' => [[]],
            'not an integer' => [['membership_id' => 'invalid']],
            'below minimum' => [['membership_id' => 0]],
        ];
    }

    public function test_nonexistent_membership_returns_404(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $membershipId = $membership->id;
        $membership->delete();

        $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membershipId,
        ])->assertNotFound();

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_uses_authenticated_user_and_session_from_route(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $otherUser = User::factory()->create();
        $otherSession = TrainingSession::factory()->create();

        $response = $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
            'user_id' => $otherUser->id,
            'training_session_id' => $otherSession->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.training_session_id', $session->id);

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseHas('bookings', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'training_session_id' => $session->id,
        ]);
    }

    public function test_member_can_cancel_and_book_the_session_again(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $session->update(['capacity' => 1]);
        $booking = Booking::factory()->for($user)->for($session, 'trainingSession')->create();

        $this->deleteJson(route('bookings.destroy', ['booking' => $booking]))->assertNoContent();

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);

        $response = $this->postJson(route('bookings.store', ['gym' => $session->gym_id, 'trainingSession' => $session]), [
            'membership_id' => $membership->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseHas('bookings', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'training_session_id' => $session->id,
        ]);
    }

    public function test_guest_cannot_cancel_a_booking_and_receives_401(): void
    {
        $session = TrainingSession::factory()->create();
        $booking = Booking::factory()->for(User::factory())->for($session, 'trainingSession')->create();

        $this->deleteJson(route('bookings.destroy', ['booking' => $booking]))->assertUnauthorized();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    public function test_cancelling_another_members_booking_returns_404(): void
    {
        [$user, $session] = $this->bookableSession();
        $owner = User::factory()->create();
        $booking = Booking::factory()->for($owner)->for($session, 'trainingSession')->create();

        $this->deleteJson(route('bookings.destroy', ['booking' => $booking]))->assertNotFound();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => $owner->id]);
    }

    public function test_cancelling_a_deleted_booking_returns_404(): void
    {
        [$user, $session] = $this->bookableSession();
        $booking = Booking::factory()->for($user)->for($session, 'trainingSession')->create();
        $bookingId = $booking->id;
        $booking->delete();

        $this->deleteJson(route('bookings.destroy', ['booking' => $bookingId]))->assertNotFound();

        $this->assertDatabaseCount('bookings', 0);
    }

    #[DataProvider('cancellationTimes')]
    public function test_cancellation_at_or_after_session_start_returns_422(string $moment): void
    {
        [$user, $session] = $this->bookableSession();
        $booking = Booking::factory()->for($user)->for($session, 'trainingSession')->create();
        $this->travelTo(CarbonImmutable::parse($moment, 'UTC'));

        $this->deleteJson(route('bookings.destroy', ['booking' => $booking]))
            ->assertUnprocessable()->assertJsonValidationErrors('booking');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    /** @return array<string, array{string}> */
    public static function cancellationTimes(): array
    {
        return [
            'exact start' => ['2026-12-01 16:00:00'],
            'in progress' => ['2026-12-01 16:30:00'],
            'finished' => ['2026-12-01 17:00:00'],
        ];
    }

    public function test_member_can_cancel_one_second_before_session_start(): void
    {
        [$user, $session] = $this->bookableSession();
        $booking = Booking::factory()->for($user)->for($session, 'trainingSession')->create();
        $this->travelTo(CarbonImmutable::parse('2026-12-01 15:59:59', 'UTC'));

        $this->deleteJson(route('bookings.destroy', ['booking' => $booking]))->assertNoContent();

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_member_can_cancel_after_membership_expires(): void
    {
        [$user, $session, $membership] = $this->bookableSession();
        $booking = Booking::factory()->for($user)->for($session, 'trainingSession')->create();
        $membership->update(['ends_at' => '2026-11-30 10:00:00']);

        $this->deleteJson(route('bookings.destroy', ['booking' => $booking]))->assertNoContent();

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    /** @return array{User, TrainingSession, Membership} */
    private function bookableSession(): array
    {
        $now = CarbonImmutable::parse('2026-11-30 10:00:00', 'UTC');
        $this->travelTo($now);

        $user = User::factory()->create();
        $gym = Gym::factory()->create(['timezone' => 'Europe/Chisinau']);
        $plan = MembershipPlan::factory()->for($gym)->create();
        $membership = Membership::factory()->for($user)->forPlan($plan, $now)->create();
        $session = TrainingSession::factory()->for($gym)->create([
            'starts_at' => '2026-12-01 16:00:00',
            'ends_at' => '2026-12-01 17:00:00',
        ]);

        GymOpeningHour::factory()->for($gym)->create([
            'day_of_week' => 2,
            'opens_at' => '07:00',
            'closes_at' => '22:00',
        ]);

        Sanctum::actingAs($user);

        return [$user, $session, $membership];
    }
}
