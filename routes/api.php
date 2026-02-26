<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::delete('delete-old-data', [AuthController::class, 'cleanupOldStocks']);

Route::middleware(['auth:sanctum', 'role:User'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('recommendations', [HomeController::class, 'recommendations']);
    Route::get('alerts', [HomeController::class, 'alerts']);
    Route::get('blogs', [HomeController::class, 'blogs']);

    Route::get('portfolios', [HomeController::class, 'portfolios']);
    Route::post('portfolios', [HomeController::class, 'storePortfolio']);
    Route::put('portfolios/{id}', [HomeController::class, 'updatePortfolio']);
    Route::delete('portfolios/{id}', [HomeController::class, 'deletePortfolio']);

    Route::get('stocks', [HomeController::class, 'stocks']);
    Route::get('traded-stocks', [HomeController::class, 'tradedStocks']);

    Route::get('sectors', [HomeController::class, 'sectors']);
    Route::get('top20-gainers/{sector}', [HomeController::class, 'top20Gainers']);
    Route::get('top20-loosers/{sector}', [HomeController::class, 'top20Loosers']);
});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/user', [AuthController::class, 'profile']);
});
