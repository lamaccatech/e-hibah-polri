<?php

use App\Livewire\MabesAgreementReview\Index;
use App\Livewire\MabesAgreementReview\Review;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'mabes'])->group(function () {
    Route::livewire('mabes-agreement-review', Index::class)->name('mabes-agreement-review.index');
    Route::livewire('mabes-agreement-review/{grant}/review', Review::class)->name('mabes-agreement-review.review');
});
