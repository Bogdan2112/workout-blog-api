<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class WeekApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_guest_cannot_view_weeks(): void
    {
        $response = $this->getJson('/api/v1/weeks');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_weeks():void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/weeks');

        $response->assertOk();
    }
}
