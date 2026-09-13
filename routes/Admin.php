<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostImageController;
use App\Http\Controllers\RealizationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)
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

            Route::get('/realizations', [RealizationController::class, 'index'])
                ->name('realizations.index');

            Route::get('/realizations/create', [RealizationController::class, 'create'])
                ->name('realizations.create');

            Route::post('/realizations', [RealizationController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('realizations.store');

            Route::get('/realizations/{realization}/edit', [RealizationController::class, 'edit'])
                ->name('realizations.edit');

            Route::post('/realizations/{realization}/images', [RealizationController::class, 'storeImages'])
                ->middleware('throttle:10,1')
                ->name('realizations.images.store');

            Route::delete('/realizations/{realization}/images/{image}', [RealizationController::class, 'destroyImage'])
                ->scopeBindings()
                ->name('realizations.images.destroy');

            Route::delete('/realizations/{realization}/cover', [RealizationController::class, 'destroyCover'])
                ->name('realizations.cover.destroy');

            Route::delete('/realizations/{realization}', [RealizationController::class, 'destroy'])
                ->name('realizations.destroy');
        });
});
