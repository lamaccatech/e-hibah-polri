<?php

use App\Livewire\ActivityLog\Index;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'mabes'])->group(function () {
    Route::livewire('activity-log', Index::class)->name('activity-log.index');
});
