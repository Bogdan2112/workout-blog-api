<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\TrainingSessionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TrainingSessionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_consult_sessions(): void
    {
        $session = TrainingSession::factory()->create();

        $this->getJson(route('training-sessions.index', ['gym' => $session->gym_id]))->assertUnauthorized();
        $this->getJson(route('training-sessions.show', ['gym' => $session->gym_id, 'trainingSession' => $session->id]))->assertUnauthorized();
    }

    public function test_member_without_membership_sees_only_selected_gym_and_local_day_in_order(): void
    {
        $gym = Gym::factory()->create(['timezone' => 'Europe/Chisinau']);
        $later = TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-11-24 18:00:00', 'ends_at' => '2026-11-24 19:00:00']);
        $first = TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-11-23 22:00:00', 'ends_at' => '2026-11-23 23:00:00']);
        TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-11-24 22:00:00', 'ends_at' => '2026-11-24 23:00:00']);
        TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-11-23 21:00:00', 'ends_at' => '2026-11-23 22:00:00']);
        TrainingSession::factory()->create(['starts_at' => '2026-11-24 18:00:00']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.index', ['gym' => $gym, 'date' => '2026-11-24']))
            ->assertOk()->assertJsonPath('data.*.id', [$first->id, $later->id])
            ->assertJsonPath('data.0.starts_at', '2026-11-23T22:00:00.000000Z')
            ->assertJsonPath('data.0.capacity', 15)->assertJsonPath('meta.total', 2);
    }

    public function test_default_date_uses_gym_timezone_and_survives_pagination(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-23 23:00:00', 'UTC'));
        $gym = Gym::factory()->create(['timezone' => 'Europe/Chisinau']);
        $first = TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-11-24 08:00:00', 'ends_at' => '2026-11-24 09:00:00']);
        $second = TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-11-24 09:00:00', 'ends_at' => '2026-11-24 10:00:00']);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson(route('training-sessions.index', ['gym' => $gym, 'per_page' => 1]));

        $response->assertOk()->assertJsonPath('data.*.id', [$first->id])->assertJsonPath('meta.total', 2);
        $this->getJson($response->json('links.next'))->assertOk()->assertJsonPath('data.*.id', [$second->id])->assertJsonPath('meta.per_page', 1);
    }

    public function test_date_filter_respects_daylight_saving_boundary(): void
    {
        $gym = Gym::factory()->create(['timezone' => 'Europe/Chisinau']);
        $inside = TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-03-29 20:59:59', 'ends_at' => '2026-03-29 21:59:59']);
        TrainingSession::factory()->for($gym)->create(['starts_at' => '2026-03-29 21:00:00', 'ends_at' => '2026-03-29 22:00:00']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.index', ['gym' => $gym, 'date' => '2026-03-29']))
            ->assertOk()->assertJsonPath('data.*.id', [$inside->id]);
    }

    public function test_selected_day_without_sessions_returns_empty_list(): void
    {
        $gym = Gym::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.index', ['gym' => $gym, 'date' => '2026-11-24']))
            ->assertOk()->assertJsonPath('data', [])->assertJsonPath('meta.total', 0);
    }

    public function test_details_include_description_and_cancellation_without_membership(): void
    {
        $session = TrainingSession::factory()->cancelled()->create([
            'description' => 'Pilates pentru începători.',
            'cancelled_at' => '2026-11-23 12:00:00',
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.show', ['gym' => $session->gym_id, 'trainingSession' => $session->id]))
            ->assertOk()->assertJsonPath('data.description', 'Pilates pentru începători.')
            ->assertJsonPath('data.cancelled_at', '2026-11-23T12:00:00.000000Z');
    }

    public function test_session_is_not_exposed_under_another_gym(): void
    {
        $session = TrainingSession::factory()->create();
        $otherGym = Gym::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.show', ['gym' => $otherGym, 'trainingSession' => $session]))->assertNotFound();
    }

    public function test_inactive_gym_hides_its_sessions(): void
    {
        $gym = Gym::factory()->create(['is_active' => false]);
        $session = TrainingSession::factory()->for($gym)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.index', ['gym' => $gym]))->assertNotFound();
        $this->getJson(route('training-sessions.index', ['gym' => $gym, 'date' => 'invalid']))->assertNotFound();
        $this->getJson(route('training-sessions.show', ['gym' => $gym, 'trainingSession' => $session]))->assertNotFound();
    }

    /** @return array<string, array{mixed}> */
    public static function invalidDates(): array
    {
        return [
            'wrong format' => ['24-11-2026'],
            'impossible day' => ['2026-02-30'],
            'array' => [['2026-11-24']],
            'SQL text' => ["2026-11-24' OR 1=1 --"],
        ];
    }

    #[DataProvider('invalidDates')]
    public function test_invalid_date_returns_422(mixed $date): void
    {
        $gym = Gym::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.index', ['gym' => $gym, 'date' => $date]))
            ->assertUnprocessable()->assertJsonValidationErrors(['date'])
            ->assertJsonPath('errors.date.0', 'The date field must match the format Y-m-d.');
    }

    public function test_empty_date_returns_422(): void
    {
        $gym = Gym::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('training-sessions.index', ['gym' => $gym, 'date' => '']))
            ->assertUnprocessable()->assertJsonValidationErrors(['date'])
            ->assertJsonPath('errors.date.0', 'The date field is required.');
    }

    public function test_demo_seeder_is_repeatable_and_only_adds_sessions_for_active_demo_gyms(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-24 12:00:00', 'UTC'));
        $owner = User::factory()->create(['name' => 'Proprietar Fitline Demo']);
        $gym = Gym::factory()->for($owner, 'owner')->create(['timezone' => 'Europe/Chisinau']);
        Gym::factory()->for($owner, 'owner')->create(['is_active' => false]);
        Gym::factory()->create();

        $this->seed(TrainingSessionSeeder::class);
        $this->seed(TrainingSessionSeeder::class);

        $this->assertDatabaseCount('training_sessions', 1);
        $this->assertDatabaseHas('training_sessions', [
            'gym_id' => $gym->id,
            'starts_at' => '2026-11-25 14:00:00',
            'ends_at' => '2026-11-25 15:00:00',
            'capacity' => 15,
        ]);
    }
}
