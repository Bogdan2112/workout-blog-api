<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Services\GymOpeningHourService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GymOpeningHourTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_schedule_can_be_updated_without_duplicate_days(): void
    {
        $gym = Gym::factory()->create();
        $service = app(GymOpeningHourService::class);
        $schedule = $this->schedule();
        $schedule[6] = ['day_of_week' => 7, 'opens_at' => null, 'closes_at' => null];

        $service->setWeeklySchedule($gym, $schedule);
        $schedule[5]['closes_at'] = '18:00';
        $service->setWeeklySchedule($gym, $schedule);

        $this->assertDatabaseCount('gym_opening_hours', 7);
        $this->assertSame(7, $gym->openingHours->last()->day_of_week);
        $this->assertNull($gym->openingHours->last()->opens_at);
        $this->assertSame('18:00', substr($gym->openingHours->firstWhere('day_of_week', 6)->closes_at, 0, 5));
    }

    #[DataProvider('invalidDays')]
    public function test_invalid_schedule_is_rejected_without_saving(array $day): void
    {
        $gym = Gym::factory()->create();
        $schedule = $this->schedule();
        $schedule[0] = $day;

        try {
            app(GymOpeningHourService::class)->setWeeklySchedule($gym, $schedule);
            $this->fail('Programul invalid a fost acceptat.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }

        $this->assertDatabaseEmpty('gym_opening_hours');
    }

    /** @return array<string, array{array{day_of_week: int, opens_at: ?string, closes_at: ?string}}> */
    public static function invalidDays(): array
    {
        return [
            'day outside range' => [['day_of_week' => 0, 'opens_at' => '07:00', 'closes_at' => '22:00']],
            'duplicate day' => [['day_of_week' => 2, 'opens_at' => '07:00', 'closes_at' => '22:00']],
            'missing closing time' => [['day_of_week' => 1, 'opens_at' => '07:00', 'closes_at' => null]],
            'missing opening time' => [['day_of_week' => 1, 'opens_at' => null, 'closes_at' => '22:00']],
            'overnight interval' => [['day_of_week' => 1, 'opens_at' => '22:00', 'closes_at' => '07:00']],
            'empty interval' => [['day_of_week' => 1, 'opens_at' => '07:00', 'closes_at' => '07:00']],
            'invalid hour' => [['day_of_week' => 1, 'opens_at' => '25:00', 'closes_at' => '26:00']],
        ];
    }

    /** @return array<int, array{day_of_week: int, opens_at: string, closes_at: string}> */
    private function schedule(): array
    {
        return array_map(fn (int $day): array => [
            'day_of_week' => $day, 'opens_at' => '07:00', 'closes_at' => '22:00',
        ], range(1, 7));
    }
}
