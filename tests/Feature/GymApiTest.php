<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GymApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_gyms(): void
    {
        $response = $this->getJson(route('gyms.index'));

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_sees_only_active_gyms(): void
    {
        $user = User::factory()->create();

        $activeGym = Gym::factory()->for($user, 'owner')->create();

        Gym::factory()->for($user, 'owner')->create([
            'is_active' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('gyms.index'));

        $response->assertOk();
        $response->assertJsonPath('data', [
            [
                'id' => $activeGym->id,
                'name' => $activeGym->name,
                'city' => $activeGym->city,
            ],
        ]);
    }

    public function test_gyms_are_sorted_by_name_then_id(): void
    {
        $user = User::factory()->create();

        $zetaGym = Gym::factory()->for($user, 'owner')->create([
            'name' => 'Zeta Fitness',
        ]);

        $firstAlphaGym = Gym::factory()->for($user, 'owner')->create([
            'name' => 'Alpha Fitness',
        ]);

        $secondAlphaGym = Gym::factory()->for($user, 'owner')->create([
            'name' => 'Alpha Fitness',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('gyms.index'));

        $response->assertOk();
        $response->assertJsonPath('data.*.id', [
            $firstAlphaGym->id,
            $secondAlphaGym->id,
            $zetaGym->id,
        ]);
    }

    public function test_city_filter_returns_only_active_matching_gyms_and_preserves_pagination(): void
    {
        $user = User::factory()->create();
        $firstGym = Gym::factory()->for($user, 'owner')->create(['city' => 'Bălți', 'name' => 'Alpha Fitness']);
        $secondGym = Gym::factory()->for($user, 'owner')->create(['city' => 'Bălți', 'name' => 'Beta Fitness']);
        Gym::factory()->for($user, 'owner')->create(['city' => 'Chișinău', 'name' => 'Another Gym']);
        Gym::factory()->for($user, 'owner')->create(['city' => 'Bălți', 'is_active' => false]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('gyms.index', ['city' => 'Bălți', 'per_page' => 1]));

        $response->assertOk()
            ->assertJsonPath('data.*.id', [$firstGym->id])
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1);
        $nextPage = $response->json('links.next');

        $this->getJson($nextPage)->assertOk()
            ->assertJsonPath('data.*.id', [$secondGym->id])
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1);
    }

    public function test_city_filter_returns_an_empty_list_when_no_gym_matches(): void
    {
        $user = User::factory()->create();
        Gym::factory()->for($user, 'owner')->create(['city' => 'Chișinău']);
        Sanctum::actingAs($user);

        $this->getJson(route('gyms.index', ['city' => 'Cahul']))
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0);
    }

    public function test_empty_city_filter_keeps_gyms_from_all_cities(): void
    {
        $user = User::factory()->create();
        Gym::factory()->for($user, 'owner')->create(['city' => 'Chișinău']);
        Gym::factory()->for($user, 'owner')->create(['city' => 'Bălți']);
        Sanctum::actingAs($user);

        $this->getJson(route('gyms.index', ['city' => '']))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_city_filter_returns_422_for_an_array(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('gyms.index', ['city' => ['Chișinău']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['city'])
            ->assertJsonPath('errors.city.0', 'The city field must be a string.');
    }

    public function test_city_filter_returns_422_when_longer_than_255_characters(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('gyms.index', ['city' => str_repeat('a', 256)]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['city'])
            ->assertJsonPath('errors.city.0', 'The city field must not be greater than 255 characters.');
    }
}
