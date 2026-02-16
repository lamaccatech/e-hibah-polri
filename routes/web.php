<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/user-management.php';
require __DIR__.'/chief-management.php';
require __DIR__.'/grant-planning.php';
require __DIR__.'/grant-review.php';
require __DIR__.'/mabes-grant-review.php';
