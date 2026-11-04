<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/projects', ProjectController::class);
Route::post('/contact', ContactController::class)->middleware('throttle:5,1');

Route::prefix('v1')->group(function (): void {
    Route::post('/contact', ContactController::class)->middleware('throttle:5,1');
});
