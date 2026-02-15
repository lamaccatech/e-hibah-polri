<?php

use App\Livewire\GrantReview\Index;
use App\Livewire\GrantReview\Review;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'polda'])->group(function () {
    Route::livewire('grant-review', Index::class)->name('grant-review.index');
    Route::livewire('grant-review/{grant}/review', Review::class)->name('grant-review.review');
});
