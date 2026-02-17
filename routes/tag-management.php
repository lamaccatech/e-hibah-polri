<?php

use App\Livewire\TagManagement\Index;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'mabes'])->group(function () {
    Route::livewire('tag', Index::class)->name('tag.index');
});
