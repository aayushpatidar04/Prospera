<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum', 'role:User'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('recommendations', [HomeController::class, 'recommendations']);
});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/user', [AuthController::class, 'profile']);
});
