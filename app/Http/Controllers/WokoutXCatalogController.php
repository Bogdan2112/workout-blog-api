<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Services\WorkoutXExerciseService;
use App\Services\ExerciseCatalog;
use Illuminate\Http\Client\ConnectionException;

class WokoutXCatalogController extends Controller
{
    // public function index(Request $request, WorkoutXExerciseService $workoutx)
    // {
    //     $data = $request->validate([
    //         'q' => 'required|string|min:2|max:100',
    //     ]);

    //     $response = $workoutx->search($data['q']);

    //     if($response->failed()) {
    //         return response()->json([
    //             'message' => 'Nu s-a putut incarca exercitiile',
    //         ], 503);
    //     }

    //     return response()->json($response->json());
    // }

     public function index(Request $request, ExerciseCatalog $workoutx)
    {
        $data = $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        try {
            $response = $workoutx->search($data['q']);
        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'Serviciul WorkoutX nu este disponibil momentan.',
            ], 503);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Nu s-au putut încărca exercițiile.',
            ], 503);
        }

        return response()->json($response->json());
    }
}
