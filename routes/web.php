<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\PublicRealizationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::inertia('/kontakt', 'ContactPage')->name('contact-page');

Route::inertia('/faq', 'Faq')->name('faq');

Route::get('/blog', [PublicPostController::class, 'index'])->name('blog');
Route::get('/blog/{post:slug}', [PublicPostController::class, 'show'])->name('blog.show');
Route::get('/realizacje/{realization}', [PublicRealizationController::class, 'show'])
    ->name('realizations.show');

require __DIR__.'/settings.php';
require __DIR__.'/Admin.php';
