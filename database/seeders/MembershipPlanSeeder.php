<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $gyms = Gym::query()->whereHas('owner', fn (Builder $query): Builder => $query->where('name', 'Proprietar Fitline Demo'))->get();

        $types = [
            ['name' => 'Part Time', 'days' => [1, 2, 3, 4, 5], 'end' => '17:00', 'amount' => 40000, 'description' => 'Luni–vineri, de la deschidere până la 17:00, în limitele programului sălii.'],
            ['name' => 'Full Time', 'days' => [1, 2, 3, 4, 5, 6, 7], 'end' => null, 'amount' => 60000, 'description' => 'Toate zilele, în programul de funcționare al sălii.'],
            ['name' => 'Weekend', 'days' => [6, 7], 'end' => null, 'amount' => 25000, 'description' => 'Sâmbătă și duminică, în programul sălii.'],
        ];

        foreach ($gyms as $gym) {
            foreach ($types as $type) {
                foreach ([1, 3, 6, 12] as $months) {
                    $gym->membershipPlans()->firstOrCreate(
                        ['name' => $type['name'].' — '.$months.($months === 1 ? ' lună' : ' luni')],
                        [
                            'description' => $type['description'].' O lună reprezintă 30 de zile. Preț demonstrativ.',
                            'image_path' => 'gyms/demo/fitness.jpg',
                            'price_amount' => $type['amount'] * $months,
                            'currency' => 'MDL',
                            'duration_days' => $months * 30,
                            'allowed_days' => $type['days'],
                            'access_ends_at' => $type['end'],
                            'is_active' => true,
                        ],
                    );
                }
            }
        }
    }
}
