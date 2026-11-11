<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\Week;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class FrontendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_api_status_without_rendering_the_frontend(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertExactJson(['name' => 'Repwise API', 'status' => 'ok']);
    }

    #[TestWith(['http://localhost:5173'])]
    #[TestWith(['http://127.0.0.1:5173'])]
    public function test_separate_frontend_can_preflight_requests_with_authorization_header(string $origin): void
    {
        $this->withHeaders([
            'Origin' => $origin,
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'authorization,content-type',
        ])->options('/api/v1/weeks')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', $origin)
            ->assertHeader('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type');
    }

    public function test_other_origins_are_not_allowed_by_cors(): void
    {
        $this->withHeaders([
            'Origin' => 'https://other.example',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/api/login')
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_registration_login_and_logout_support_bearer_authentication(): void
    {
        $credentials = ['email' => 'repwise@example.test', 'password' => 'repwise-test-password'];
        $this->postJson('/api/register', ['name' => 'Repwise User', ...$credentials])
            ->assertCreated()
            ->assertJsonPath('user.email', $credentials['email']);

        $login = $this->postJson('/api/login', $credentials)
            ->assertOk()
            ->assertJsonStructure(['token']);
        $token = $login->json('token');
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('name', 'Repwise User');
        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();

        $this->assertDatabaseEmpty('personal_access_tokens');
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_journal_supports_the_complete_workout_lifecycle(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $week = $this->postJson('/api/v1/weeks', ['name' => 'Săptămâna 1'])
            ->assertCreated()->json('id');
        $workout = $this->postJson("/api/v1/weeks/{$week}/workouts", ['name' => 'Picioare'])
            ->assertCreated()->json('id');
        $exercise = $this->postJson("/api/v1/workouts/{$workout}/exercises", ['name' => 'Genuflexiuni'])
            ->assertCreated()->json('id');
        $set = $this->postJson("/api/v1/exercises/{$exercise}/sets", ['kg' => 60, 'reps' => 8])
            ->assertCreated()->json('id');

        $this->putJson("/api/v1/weeks/{$week}", ['name' => 'Săptămâna actualizată'])->assertOk();
        $this->putJson("/api/v1/workouts/{$workout}", ['name' => 'Picioare A'])->assertOk();
        $this->putJson("/api/v1/exercises/{$exercise}", ['name' => 'Squat'])->assertOk();
        $this->patchJson("/api/v1/sets/{$set}", ['kg' => 62.5, 'reps' => 10])->assertOk();

        $this->getJson('/api/v1/weeks')->assertOk()->assertJsonPath('data.0.name', 'Săptămâna actualizată');
        $this->getJson("/api/v1/weeks/{$week}/workouts")->assertOk()->assertJsonPath('0.name', 'Picioare A');
        $this->getJson("/api/v1/workouts/{$workout}/exercises")->assertOk()->assertJsonPath('0.name', 'Squat');
        $this->getJson("/api/v1/exercises/{$exercise}/sets")->assertOk()->assertJsonPath('0.reps', 10);
        $this->assertDatabaseHas('sets', ['id' => $set, 'kg' => 62.5, 'reps' => 10]);

        $this->deleteJson("/api/v1/sets/{$set}")->assertOk();
        $this->assertDatabaseMissing('sets', ['id' => $set]);
        $this->deleteJson("/api/v1/exercises/{$exercise}")->assertOk();
        $this->assertDatabaseMissing('exercises', ['id' => $exercise]);
        $this->deleteJson("/api/v1/workouts/{$workout}")->assertOk();
        $this->assertDatabaseMissing('workouts', ['id' => $workout]);
        $this->assertDatabaseHas('weeks', ['id' => $week]);
        $this->deleteJson("/api/v1/weeks/{$week}")->assertOk();
        $this->assertDatabaseMissing('weeks', ['id' => $week]);
    }

    public function test_deleting_a_workout_also_removes_its_exercises_and_sets(): void
    {
        $week = Week::factory()->for(User::factory())->create();
        $workout = Workout::factory()->for($week)->has(Exercise::factory()->hasSets(1))->create();
        Sanctum::actingAs($week->user);

        $this->deleteJson("/api/v1/workouts/{$workout->id}")->assertOk();

        $this->assertModelMissing($workout);
        $this->assertModelExists($week);
        $this->assertDatabaseEmpty('exercises');
        $this->assertDatabaseEmpty('sets');
    }

    public function test_workout_details_include_exercises_and_sets_in_one_response(): void
    {
        $week = Week::factory()->for(User::factory())->create();
        $workout = Workout::factory()->for($week)->has(Exercise::factory()->hasSets(2))->create();
        Sanctum::actingAs($week->user);
        $exercise = $workout->exercises->first();

        $this->getJson("/api/v1/workouts/{$workout->id}")
            ->assertOk()
            ->assertJsonPath('id', $workout->id)
            ->assertJsonPath('exercises.0.id', $exercise->id)
            ->assertJsonCount(2, 'exercises.0.sets')
            ->assertJsonPath('exercises.0.sets.0.exercise_id', $exercise->id);
    }

    public function test_workout_details_return_403_for_another_user(): void
    {
        $workout = Workout::factory()->for(Week::factory()->for(User::factory()))
            ->has(Exercise::factory()->hasSets(1))->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/v1/workouts/{$workout->id}")
            ->assertForbidden()
            ->assertJsonMissingPath('exercises');
    }

    public function test_listing_weeks_does_not_query_workouts_once_per_week(): void
    {
        $user = User::factory()->create();
        Week::factory()->count(10)->for($user)->hasWorkouts(1)->create();
        Sanctum::actingAs($user);
        $this->expectsDatabaseQueryCount(3);

        $this->getJson('/api/v1/weeks')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonCount(1, 'data.0.workouts');
    }

    public function test_returns_403_when_deleting_another_users_workout(): void
    {
        $workout = Workout::factory()->for(Week::factory()->for(User::factory()))->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/v1/workouts/{$workout->id}")->assertForbidden();

        $this->assertModelExists($workout);
    }

    public function test_workoutx_import_returns_the_saved_exercise(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.workoutxapp.com/v1/exercises/exercise/bench-press' => Http::response([
                'name' => 'Bench Press', 'target' => 'chest', 'instructions' => ['Press upwards'], 'mechanic' => 'compound',
            ]),
        ]);
        $workout = Workout::factory()->for(Week::factory()->for(User::factory()))->create();
        Sanctum::actingAs($workout->week->user);

        $response = $this->postJson("/api/v1/workouts/{$workout->id}/exercises/from-workoutx", ['workoutx_id' => 'bench-press']);

        $response->assertCreated()->assertJsonPath('name', 'Bench Press')->assertJsonPath('instructions.0', 'Press upwards');
        $this->assertDatabaseHas('exercises', [
            'id' => $response->json('id'), 'workout_id' => $workout->id, 'workoutx_id' => 'bench-press', 'target_muscle' => 'chest',
        ]);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.workoutxapp.com/v1/exercises/exercise/bench-press');
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_returns_503_without_creating_an_exercise_when_workoutx_is_unavailable(bool $connectionFailure): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.workoutxapp.com/v1/exercises/exercise/bench-press' => $connectionFailure
                ? Http::failedConnection()
                : Http::response([], 503),
        ]);
        $workout = Workout::factory()->for(Week::factory()->for(User::factory()))->create();
        Sanctum::actingAs($workout->week->user);

        $this->postJson("/api/v1/workouts/{$workout->id}/exercises/from-workoutx", ['workoutx_id' => 'bench-press'])
            ->assertServiceUnavailable();

        $this->assertDatabaseEmpty('exercises');
        Http::assertSentCount(1);
    }

    public function test_returns_403_without_contacting_workoutx_for_another_users_workout(): void
    {
        Http::preventStrayRequests();
        Http::fake();
        $workout = Workout::factory()->for(Week::factory()->for(User::factory()))->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/v1/workouts/{$workout->id}/exercises/from-workoutx", ['workoutx_id' => 'bench-press'])
            ->assertForbidden();

        $this->assertDatabaseEmpty('exercises');
        Http::assertNothingSent();
    }
}
