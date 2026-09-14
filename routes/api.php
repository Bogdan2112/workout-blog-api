<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeekController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\SetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Week routes
Route::get('/weeks', [WeekController::class, 'index']);
Route::post('/weeks', [WeekController::class, 'store']);

Route::get('/weeks/{week}',[WeekController::class, 'show']);  
Route::put('/weeks/{week}',[WeekController::class, 'update']);
Route::delete('/weeks/{week}',[WeekController::class, 'destroy']);

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