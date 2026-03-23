<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API-style routes for the UI (using session auth)
Route::prefix('api')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

    // Storage Routes (Protected by Auth)
    Route::middleware('auth')->group(function () {
        Route::post('/files', [FileController::class, 'upload']);
        Route::delete('/files/{file_id}', [FileController::class, 'delete']);
        Route::get('/storage-summary', [FileController::class, 'summary']);
        Route::get('/files', [FileController::class, 'list']);
    });
});
