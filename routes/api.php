<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeekController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/weeks', [WeekController::class, 'index']);
Route::post('/weeks', [WeekController::class, 'store']);
Route::get('/weeks/{id}',[WeekController::class, 'show']);  
Route::put('/weeks/{id}',[WeekController::class, 'update']);