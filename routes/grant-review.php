<?php

use App\Livewire\GrantReview\Index;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'polda'])->group(function () {
    Route::livewire('grant-review', Index::class)->name('grant-review.index');
});
