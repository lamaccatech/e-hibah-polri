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
require __DIR__.'/grant-agreement.php';
require __DIR__.'/agreement-review.php';
require __DIR__.'/mabes-agreement-review.php';
require __DIR__.'/grant-detail.php';
require __DIR__.'/grant-document.php';
require __DIR__.'/tag-management.php';
