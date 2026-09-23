<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Http\Models;

class CreateWorkoutReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    protected $workoutId;

    public function __construct(int $workoutId)
    {    
        $this->workoutId = $workoutId;


    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // if ($this->workoutId === -1) {
        //     throw new \RuntimeException('Eroare de test pentru job');
        // }

        Log::info('Se genereaza raportul pentru antrenament',[
            'workout_id' => $this->workoutId,
        ]);
    }
}
