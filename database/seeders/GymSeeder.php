<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\GymPhoto;
use App\Models\User;
use Illuminate\Database\Seeder;

class GymSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Proprietar Fitline Demo',
        ]);

        Gym::factory()
            ->count(2)
            ->for($owner, 'owner')
            ->has(
                GymPhoto::factory()
                    ->count(3)
                    ->sequence(
                        ['path' => 'gyms/demo/reception.jpg', 'sort_order' => 0],
                        ['path' => 'gyms/demo/fitness.jpg', 'sort_order' => 1],
                        ['path' => 'gyms/demo/exterior.jpg', 'sort_order' => 2],
                    ),
                'photos',
            )
            ->create();
    }
}
