<?php

use App\Livewire\MabesGrantReview\Index;
use App\Livewire\MabesGrantReview\Review;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'mabes'])->group(function () {
    Route::livewire('mabes-grant-review', Index::class)->name('mabes-grant-review.index');
    Route::livewire('mabes-grant-review/{grant}/review', Review::class)->name('mabes-grant-review.review');
});
