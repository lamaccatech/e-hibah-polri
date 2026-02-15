<?php

use App\Livewire\ChiefManagement\Create;
use App\Livewire\ChiefManagement\Edit;
use App\Livewire\ChiefManagement\Index;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'satker'])->group(function () {
    Route::livewire('kepala-satker', Index::class)->name('chief.index');
    Route::livewire('kepala-satker/create', Create::class)->name('chief.create');
    Route::livewire('kepala-satker/{chief}/edit', Edit::class)->name('chief.edit');
});
