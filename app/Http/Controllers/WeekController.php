<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Week;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreWeekRequest;
use App\Http\Requests\UpdateWeekRequest;
use App\Http\Resources\WeekResource;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Gate;

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
        // $weeks = Week::with('workouts.exercises')->paginate(10); //get()
        // return WeekResource::collection($weeks);

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

        // ----  ----
        // $weeks = Week::query();

        $weeks = $request->user()->weeks();

        // Filtering
         if($request->name){
            $weeks->where('name', $request->name);
        }

        // Sorting
        // $weeks->orderBy('name');
        $allowedSorts = ['name', 'created_at'];
        $allowedDirections = ['asc', 'desc'];
        // if(in_array($request->sort, $allowedSorts)){
        //     $weeks->orderBy($request->sort);
        // }

        if($request->sort){
            if(!in_array($request->sort, $allowedSorts)){
                return response()->json([
                'message' => 'Invalid sort field'
            ], 404);
            }
            if($request->direction && !in_array($request->direction, $allowedDirections)){
                return response()->json([
                    'message' => 'Invalid direction field'
                ], 404);
            }
            $weeks->orderBy(
                $request->sort,
                $request->direction ?? 'asc');
        }

        
        return WeekResource::collection($weeks->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWeekRequest $request)
    {
        $week = Week::create([
            'name' => $request->name,
            'user_id' => $request->user()->id
        ]);

        return response()->json($week, 201);
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
        // throw new Exception('Test error');
        Gate::authorize('view', $week);
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

        Gate::authorize('update', $week);

        // $week->name = $request->name;
        // $week->save();

        $week->update($request->validated());

        return response()->json($week);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Week $week)
    {   
        Gate::authorize('delete', $week);
        
        $week->delete();

        return response()->json([
            'message' => 'Week deleted succesfully'
        ]);
    }
}
