<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


// Storage Routes (Protected by Auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/files', [FileController::class, 'upload']);
    Route::delete('/files/{file_id}', [FileController::class, 'delete']);
    Route::get('/storage-summary', [FileController::class, 'summary']);
    Route::get('/files', [FileController::class, 'list']);
});
