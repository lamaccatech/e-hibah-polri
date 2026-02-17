<?php

use App\Livewire\DonorListing\Index;
use App\Livewire\DonorListing\Show;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'mabes'])->group(function () {
    Route::livewire('donor', Index::class)->name('donor.index');
    Route::livewire('donor/{donor}', Show::class)->name('donor.show');
});
