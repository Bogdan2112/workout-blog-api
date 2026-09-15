<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workout;
use App\Models\Exercise;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Workout $workout)
    {
        $exercises = $workout->exercises;

        return response()->json($exercises);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Workout $workout)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255'
        // ]);

        $exercise = $workout->exercises()->create([
            'name' => $request->name
        ]);

        return response()->json($exercise, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Exercise $exercise)
    {
        return response()->json($exercise);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255'
        // ]);

        $exercise->name = $request->name;
        $exercise->save();

        return response()->json($exercise);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {
        $exercise->delete();

        return response()->json([
            'message' => 'Exercise deleted successfully'
        ]);
    }
}
