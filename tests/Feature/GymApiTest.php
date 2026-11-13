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
}
