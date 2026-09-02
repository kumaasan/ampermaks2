<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::inertia('/dashboard', 'Dashboard')
        ->name('dashboard');

    Route::prefix('dashboard')
        ->name('dashboard.')
        ->group(function () {

            Route::get('/posts', [PostController::class, 'index'])
                ->name('posts.index');

            Route::get('/posts/create', [PostController::class, 'create'])
                ->name('posts.create');

            Route::post('/posts', [PostController::class, 'store'])
                ->name('posts.store');
        });
});

require __DIR__.'/settings.php';
