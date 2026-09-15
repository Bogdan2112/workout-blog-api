<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Models\Set;
use App\Http\Requests\StoreSetRequest;
use App\Http\Requests\UpdateSetRequest;

class SetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Exercise $exercise)
    {
        $sets = $exercise->sets;

        return response()->json($sets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSetRequest $request, Exercise $exercise)
    {
        // $request->validate([
        //     'kg' => 'required|numeric',
        //     'reps' => 'required|integer'
        // ]);

        $set = $exercise->sets()->create([
            'kg' => $request->kg,
            'reps' => $request->reps
        ]);

        return response()->json($set, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Set $set)
    {
        return response()->json($set);   
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSetRequest $request, Set $set)
    {
        // $request->validate([
        //     // 'kg' => 'required|numeric',
        //     // 'reps' => 'required|integer'

        //     'kg' => 'sometimes|numeric',
        //     'reps' => 'sometimes|integer'
        // ]);

        if($request->has('kg')){
            $set->kg = $request->kg;
        }
        if($request->has('reps')){
            $set->reps = $request->reps;
        }
      
        $set->save();

        return response()->json($set);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Set $set)
    {
        $set->delete();

        return response()->json([
            'message' => 'Set deleted succesfully'
        ]);
    }
}
