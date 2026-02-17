<?php

use App\Livewire\AgreementReview\Index;
use App\Livewire\AgreementReview\Review;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'polda'])->group(function () {
    Route::livewire('agreement-review', Index::class)->name('agreement-review.index');
    Route::livewire('agreement-review/{grant}/review', Review::class)->name('agreement-review.review');
});
