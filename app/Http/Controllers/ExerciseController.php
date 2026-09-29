<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workout;
use App\Models\Exercise;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use Illuminate\Support\Facades\Gate;
use App\Services\WorkoutXExerciseService;
use Illuminate\Http\Client\ConnectionException;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Workout $workout)
    {
        Gate::authorize('view', $workout);
        $exercises = $workout->exercises;

        return response()->json($exercises);
    }   

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExerciseRequest $request, Workout $workout)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255'
        // ]);

        Gate::authorize('create', [Exercise::class, $workout]);
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
        Gate::authorize('view', $exercise); 
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
        Gate::authorize('update', $exercise);
        // $exercise->name = $request->name;
        // $exercise->save();
        $exercise->update($request->validated());
        

        return response()->json($exercise);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {   
        Gate::authorize('delete', $exercise);
        $exercise->delete();

        return response()->json([
            'message' => 'Exercise deleted successfully'
        ]);
    }

    // public function suggestions(Request $request)
    // {
    //     $data = $request->validate([
    //         'q' => 'nullable|string|max:100',
    //     ]);

    //     $search = trim($data['q'] ?? '');

    //     // ->whereRelation('workout.week', 'user_id', $request->user()->id)
    //     $names = Exercise::query()
    //         ->whereHas('workout.week', function ($query) use ($request) {
    //             $query->where('user_id', $request->user()->id);
    //         })
    //         ->when($search !== '', function ($query) use ($search) {
    //             $query->where('name', 'like', '%' . $search . '%');
    //         })
    //         ->select('name')
    //         ->distinct()
    //         ->orderBy('name')
    //         ->limit(10)
    //         ->pluck('name');

    //     return response()->json(['data' => $names]);
    // }

    public function storeFromWorkoutX(Request $request, Workout $workout, WorkoutxExerciseService $workoutx){
        Gate::authorize('create', [Exercise::class, $workout]);
        
        $validated = $request->validate([
            'workoutx_id' => 'required|string|min:2',
        ]);

        $response = $workoutx->find($validated['workoutx_id']);

        if($response->failed()){
            return response()->json([
                'message' => 'Nu am putut încărca exercițiul din WorkoutX.',
            ], 503);
        }
        
        $data = $response->json();

        $exercise = $workout->exercises()->create([
            'workoutx_id' => $validated['workoutx_id'],
            'name' => $data['name'],
            'target_muscle' => $data['target'] ?? null,
            'instructions' => $data['instructions'] ?? [],
            'mechanic' => $data['mechanic'] ?? null,
        ]);

         return response()->json($response->json($exercise, 201));
    }
}
