<?php

use App\Http\Controllers\PublicPostController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'HomePage')->name('home');

Route::inertia('/kontakt', 'ContactPage')->name('contact-page');

Route::inertia('/faq', 'Faq')->name('faq');

Route::get('/blog', [PublicPostController::class, 'index'])->name('blog');
Route::get('/blog/{post:slug}', [PublicPostController::class, 'show'])->name('blog.show');

require __DIR__.'/settings.php';
require __DIR__.'/Admin.php';
