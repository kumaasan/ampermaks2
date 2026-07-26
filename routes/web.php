<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'HomePage')->name('home');

Route::inertia('/kontakt', 'ContactPage')->name('contact-page');

Route::inertia('/faq', 'Faq')->name('faq');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
