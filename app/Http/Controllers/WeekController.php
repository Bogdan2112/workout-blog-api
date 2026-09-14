<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Week;
use Illuminate\Support\Facades\DB;

class WeekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $weeks = Week::all();

        // $weeks = DB::table('weeks')->get();

        return response()->json($weeks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

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

        return response()->json($week);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Week $week)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

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
