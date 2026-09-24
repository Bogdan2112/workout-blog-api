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

    public function test_user_sees_only_their_own_weeks():void
    {
        // test index method
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->weeks()->create(['name' => 'My week']);
        $otherUser->weeks()->create(['name' => 'Other week']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/weeks');

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $response->assertJsonFragment(['name' => 'My week']);
        $response->assertJsonMissing(['name' => 'Other week']);
    }

    public function test_user_cannot_view_another_users_week(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherWeek = $otherUser->weeks()->create([
            'name' => 'Private week'
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/weeks/'. $otherWeek->id);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_create_a_week(): void
    {
        $user = User::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/weeks', [
            'name' => 'Saptamana de test'
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('weeks',[
            'name' => 'Saptamana de test',
            'user_id' => $user->id,
        ]);
    }

    public function test_week_name_is_required():void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/weeks',[]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor('name');

        $this->assertDatabaseEmpty('weeks');
    }

    public function test_user_cannot_create_a_week_for_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/weeks',[
            'name' => 'My week',
            'user_id' => $otherUser->id,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('weeks',[
            'name' => 'My week',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('weeks',[
            'name' => 'My week',
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_user_cannot_update_another_users_week(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherWeek = $otherUser->weeks()->create([
            'name' => 'Private week',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/weeks/' . $otherWeek->id, [
            'name' => 'Changed name',
        ]);

        $response->assertForForbidden();

        $this->assertDatabaseHas('weeks',[
            'id' => $otherWeek->id,
            'name' => 'Private week',
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_user_can_update_their_own_week(): void
    {
        $user = User::factory()->create();

        $week = $user->weeks()->create([
            'name' => 'Old week',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/weeks/'. $week->id, [
            'name' => 'New name',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('weeks',[
            'id' => $week->id,
            'name' => 'New name',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_week(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherWeek =  $otherUser->weeks()->create([
            'name' => 'Private week',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/weeks/' . $otherWeek->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('weeks',[
             'id' => $otherWeek->id,
            'name' => 'Private week',
            'user_id' => $otherUser->id,
        ]);
    }
}
