<?php

use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::apiResource('events', EventController::class)->only(['index', 'show']);
    Route::apiResource('clubs', ClubController::class)->only(['index', 'show']);
});
