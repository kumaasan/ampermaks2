<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\PostImageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::inertia('/dashboard', 'Dashboard')
        ->name('dashboard');

    Route::prefix('dashboard')
        ->name('dashboard.')
        ->middleware('can:manage-posts')
        ->group(function () {

            Route::get('/posts', [PostController::class, 'index'])
                ->name('posts.index');

            Route::get('/posts/create', [PostController::class, 'create'])
                ->name('posts.create');

            Route::post('/posts', [PostController::class, 'store'])
                ->name('posts.store');

            Route::get('/posts/{post}/edit', [PostController::class, 'edit'])
                ->name('posts.edit');

            Route::put('/posts/{post}', [PostController::class, 'update'])
                ->name('posts.update');

            Route::delete('/posts/{post}', [PostController::class, 'destroy'])
                ->name('posts.destroy');

            Route::post('/post-images', [PostImageController::class, 'store'])
                ->middleware('throttle:20,1')
                ->name('post-images.store');
        });
});
