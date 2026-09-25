<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WorkoutXExerciseService;

class WokoutXCatalogController extends Controller
{
    public function index(Request $request, WorkoutXExerciseService $workoutx)
    {
        $data = $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $response = $workoutx->search($data['q']);

        if($response->failed()) {
            return response()->json([
                'message' => 'Nu s-a putut incarca exercitiile',
            ], 503);
        }

        return response()->json($response->json());
    }
}
