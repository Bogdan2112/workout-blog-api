<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class WorkoutXExerciseService
{
    public function find(string $id): Response
    {
        return Http::withHeaders([
            'X-WorkoutX-Key' => config('services.workoutx.key'),
        ])
            ->timeout(5)
            ->get("https://api.workoutxapp.com/v1/exercises/exercise/{$id}");
    }

    public function search(string $name): Response
    {
        return Http::withHeaders([
            'X-WorkoutX-Key' => config('services.workoutx.key'),
        ])
            ->timeout(5)
            ->get(
                'https://api.workoutxapp.com/v1/exercises/name/' . rawurlencode($name)
            );
    }
}