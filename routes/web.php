<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:Admin'])->name('dashboard');

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/recommendations', [HomeController::class, 'recommendations'])->name('recommendations.index');
    Route::post('/recommendations', [HomeController::class, 'storeRecommendation'])->name('recommendations.store');
    Route::patch('/recommendations/{id}', [HomeController::class, 'editRecommendation'])->name('recommendations.edit');
    Route::delete('/recommendations/{id}', [HomeController::class, 'destroyRecommendation'])->name('recommendations.destroy');
    Route::post('/recommendations/{id}', [HomeController::class, 'sendAlert'])->name('recommendations.send-alert');

    Route::get('/blogs', [HomeController::class, 'blogs'])->name('blogs.index');
    Route::post('/blogs', [HomeController::class, 'storeBlog'])->name('blogs.store');
    Route::post('/blogs/{id}', [HomeController::class, 'editBlog'])->name('blogs.edit');
    Route::delete('/blogs/{id}', [HomeController::class, 'destroyBlog'])->name('blogs.destroy');
    Route::patch('/blogs/{id}', [HomeController::class, 'publishBlog'])->name('blogs.publish');
});

require __DIR__.'/auth.php';
