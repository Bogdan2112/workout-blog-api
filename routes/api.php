<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeekController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\SetController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// User routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Week routes
// Route::get('/weeks', [WeekController::class, 'index']);
// Route::post('/weeks', [WeekController::class, 'store'])
//     ->middleware('auth:sanctum'); // POST /api/weeks poate fi accesat doar de un user autentificat.

Route::prefix('v1')
    ->middleware('auth:sanctum')
    ->group(function(){

// Log out
    Route::post('/logout', [AuthController::class, 'logout']);

// Week routes
    Route::get('/weeks', [WeekController::class, 'index']);
    Route::post('/weeks', [WeekController::class, 'store']);
    Route::get('/weeks/{week}', [WeekController::class, 'show']);
    Route::put('/weeks/{week}', [WeekController::class, 'update']);
    Route::delete('/weeks/{week}', [WeekController::class, 'destroy']);

// Workout routes
    Route::get('/weeks/{week}/workouts', [WorkoutController::class, 'index']);
    Route::post('/weeks/{week}/workouts', [WorkoutController::class, 'store']);

    Route::get('/workouts/{workout}', [WorkoutController::class, 'show']);
    Route::put('/workouts/{workout}', [WorkoutController::class, 'update']);
    Route::delete('/workouts/{workout}', [WorkoutController::class, 'destroy']);

//Exercises routes
    Route::get('/workouts/{workout}/exercises',[ExerciseController::class, 'index']);
    Route::post('/workouts/{workout}/exercises',[ExerciseController::class, 'store']);

    Route::get('/exercises/{exercise}', [ExerciseController::class, 'show']);
    Route::put('/exercises/{exercise}', [ExerciseController::class, 'update']);
    Route::delete('/exercises/{exercise}', [ExerciseController::class, 'destroy']);

    // Sets routes
    Route::get('/exercises/{exercise}/sets', [SetController::class, 'index']);
    Route::post('/exercises/{exercise}/sets', [SetController::class, 'store']);

    Route::get('/sets/{set}', [SetController::class, 'show']);
    Route::put('/sets/{set}', [SetController::class, 'update']);
    Route::delete('/sets/{set}', [SetController::class, 'destroy']);

    Route::patch('/sets/{set}', [SetController::class, 'update']);

    // Gate
    Route::get('/test-gate', function () {

    Gate::authorize('test-gate');

    return response()->json([
        'message' => 'Gate passed'
    ]);
    });

    // Test

//     Route::get('/test-permission', function (Request $request) {

//     if (!$request->user()->can('delete posts')) {
//         abort(403);
//     }

//     return response()->json([
//         'message' => 'Permission passed'
//     ]);
// });

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/admin-test', function () {
        return response()->json([
            'message' => 'Permission passed'
        ]);
    })->middleware('can:delete posts');

});

Route::get('/admin-only', function () {
    return response()->json([
        'message' => 'Admin access granted'
    ]);
})->middleware('role:admin');

// Throttling
    Route::get('/rate-test', function(){
        return response()->json([
            'message' => 'Request accepted'
        ]);
    })->middleware('throttle:5,1');

    Route::get('/rate-test-2', function(){
        return response()->json([
            'message' => 'Request accepted'
        ]);
    })->middleware('throttle:api');

});

Route::prefix('v2')
    ->middleware('auth:sanctum')
    ->group(function(){

        Route::get('/weeks', function(Request $request){
            return response()->json([
                'version' => 'v2',
                'data' => $request->user()->weeks()->get()
            ]);
        });

    });

Route::get('/header-version/weeks', function(Request $request){
    // Route::get('/weeks', function(Request $request)
    $version = $request->header('API-Version');

    if($version == 1){
        return response()->json([
            'version' => 'v1',
            'data' => $request->user()->weeks()->get()
        ]);
    }   

    if($version == 2){
        return response()->json([
            'verion' => 'v2',
            'data' => $request->user()
                ->weeks()
                ->with('workouts')
                ->get()
        ]);
    }

    return response()->json([
        'message' => 'unsuported API verison'
    ]);
})->middleware('auth:sanctum');


// 12|wV68tOfaUi2WZn2dw7wXOFQMKSDQVvPBt42rMo4Lc9d5db26

