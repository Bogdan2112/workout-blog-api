<?php

namespace Database\Seeders;

use App\Models\Gym;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class TrainingSessionSeeder extends Seeder
{
    public function run(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        $gyms = Gym::active()->whereHas('owner', fn (Builder $query): Builder => $query->where('name', 'Proprietar Fitline Demo'))->get();

        foreach ($gyms as $gym) {
            $startsAt = CarbonImmutable::now($gym->timezone)->addDay()->setTime(16, 0)->utc();
            $gym->trainingSessions()->firstOrCreate(
                ['name' => 'Pilates — demonstrație', 'starts_at' => $startsAt],
                [
                    'description' => 'Ședință demonstrativă de Pilates, cu durata de o oră.',
                    'ends_at' => $startsAt->addHour(),
                    'capacity' => 15,
                ],
            );
        }
    }
}
