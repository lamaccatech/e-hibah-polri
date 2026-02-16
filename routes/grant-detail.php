<?php

use App\Livewire\GrantDetail\Show;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('grant-detail/{grant}', Show::class)->name('grant-detail.show');
});
