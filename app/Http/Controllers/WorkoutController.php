<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutRequest;
use App\Http\Requests\UpdateWorkoutRequest;
use App\Models\Week;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class WorkoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Week $week)
    {
        Gate::authorize('view', $week);
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
        Gate::authorize('create', [Workout::class, $week]);

        $workout = $week->workouts()->create([
            'name' => $request->name,
        ]);

        // Cache::forget('week_' . $week->id);

        return response()->json($workout, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Workout $workout)
    {

        Gate::authorize('view', $workout);

        return response()->json($workout->load('exercises.sets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkoutRequest $request, Workout $workout)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255'
        // ]);

        Gate::authorize('update', $workout);

        // $workout->name = $request->name;
        // $workout->save();

        $workout->update($request->validated());

        // Cache::forget('week_' . $workout->week_id);
        return response()->json($workout);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workout $workout)
    {
        Gate::authorize('delete', $workout);

        // $weekId = $workout->week_id;

        $workout->delete();
        // Cache::forget('week_' . $weekId);

        return response()->json([
            'message' => 'Workout deleted succesfully',
        ]);
    }
}
