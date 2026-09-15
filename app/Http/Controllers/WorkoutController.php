<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Week;    
use App\Models\Workout;
use App\Http\Requests\StoreWorkoutRequest;
use App\Http\Requests\UpdateWorkoutRequest;

class WorkoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Week $week)
    {
        $workouts = $week->workouts;

        // $workouts = DB::table('workouts')
        // ->where('week_id', $week->id)
        // ->get();

        return response()->json($workouts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkoutRequest $request, Week $week) // Request $request
    {
        // $request->validate([
        //     'name' => 'required|string|max:255'
        // ]);

        $workout = $week->workouts()->create([
            'name' => $request->name,
        ]);

        return response()->json($workout, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Workout $workout)
    {
        return response()->json($workout);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkoutRequest $request, Workout $workout)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255'
        // ]);

        $workout->name = $request->name;
        $workout->save();

        return response()->json($workout);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workout $workout)
    {
        $workout->delete();

        return response()->json([
            'message' => 'Workout deleted succesfully'
        ]);
    }
}
