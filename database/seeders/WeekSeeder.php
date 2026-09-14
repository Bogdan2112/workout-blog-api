<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Week;
use App\Models\Workout; 
use App\Models\User;

class WeekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     // Week::create([
    //     //     'name' => 'Push week',
    //     //     'user_id' => '1'
    //     // ]);
    //     // Week::factory()->count(10)->create();
    //     $week = Week::factory()->create();

    //     $week->workouts()->create(
    //         Workout::factory()->make()->toArray()
    //     );
    // }

    public function run(): void
{
    $user = User::factory()->create();

    $weeks = Week::factory()
        ->count(10)
        ->create([
            'user_id' => $user->id
        ]);

    foreach ($weeks as $week) {
        Workout::factory()
            ->count(3)
            ->create([
                'week_id' => $week->id
            ]);
    }
}
}
