<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Week;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreWeekRequest;
use App\Http\Requests\UpdateWeekRequest;
use App\Http\Resources\WeekResource;

class WeekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        \DB::enableQueryLog();

        // $weeks = Week::all();

        // $weeks = DB::table('weeks')->get();

        // return response()->json($weeks);

        // ---- Eager loading ----

        //$weeks = Week::with('workouts')->get();
        $weeks = Week::with('workouts.exercises')->paginate(10); //get()
        return WeekResource::collection($weeks);

        // return response()->json([
        //     'weeks' => $weeks,
        //     'queries' => \DB::getQueryLog(), 
        // ]);
        // ----------

        // ---- Lazy loading ----
        // foreach($weeks as $week){
        //     $workouts = $week->workouts;
        // }

        // return response()->json([
        //     'queries' => \DB::getQueryLog()
        // ]);
        // ----------


        // ---- load() ----
        // $weeks = Week::all();

        // $weeks->load('workouts.exercises');

        // return response()->json([
        //     'weeks' => $weeks,
        //     'queries' => \DB::getQueryLog()
        // ]);
        // --------


        // ---- Filtering ----
        // $weeks = Week::query();

        // if($request->name){
        //     $weeks->where('name', $request->name);
        // }

        // return WeekResource::collection($weeks->get()); //return WeekResource::collection($weeks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWeekRequest $request)
    {
        $week = Week::create([
            'name' => $request->name,
            'user_id' => 1
        ]);

        return response()->json($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Week $week) // string $id
    {
        // $week = Week::find($id);
        
        // if(!$week){
        //     return response()->json([
        //         'message' => 'Week not found'
        //     ], 404);
        // }

        // return response()->json($week);
        $week->load('workouts');
        return new WeekResource($week);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWeekRequest $request, Week $week)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255'
        // ]);

        $week->name = $request->name;
        $week->save();

        return response()->json($week);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Week $week)
    {   
        $week->delete();

        return response()->json([
            'message' => 'Week deleted succesfully'
        ]);
    }
}
