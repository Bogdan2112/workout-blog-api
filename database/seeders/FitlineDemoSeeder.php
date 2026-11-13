<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FitlineDemoSeeder extends Seeder
{
    public function run(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        DB::transaction(function (): void {
            if (! Gym::query()->whereHas('owner', fn (Builder $query): Builder => $query->where('name', 'Proprietar Fitline Demo'))->exists()) {
                $this->call(GymSeeder::class);
            }

            $this->call([
                GymOpeningHourSeeder::class,
                MembershipPlanSeeder::class,
                MembershipSeeder::class,
            ]);
        });
    }
}
