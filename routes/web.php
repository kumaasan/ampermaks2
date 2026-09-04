<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'HomePage')->name('home');

Route::inertia('/kontakt', 'ContactPage')->name('contact-page');

Route::inertia('/faq', 'Faq')->name('faq');

Route::inertia('/blog', 'Blog')->name('blog');

require __DIR__.'/settings.php';
require __DIR__.'/Admin.php';
