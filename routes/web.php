<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): JsonResponse {
    return response()->json([
        'name' => 'Repwise API',
        'status' => 'ok',
    ]);
})->name('home');
