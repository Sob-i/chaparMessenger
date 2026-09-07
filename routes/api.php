<?php

use App\Http\Controllers\Api\V1\AuthController;

use Illuminate\Support\Facades\Route;

Route::post('api/register', [AuthController::class, 'register']);

Route::post('api/login', [AuthController::class, 'login']);

Route::prefix('')->middleware('auth:sanctum')->group(function () {
    Route::post('api/logout', [AuthController::class, 'logout']);
});
