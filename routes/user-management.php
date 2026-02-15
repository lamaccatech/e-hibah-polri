<?php

use App\Livewire\UserManagement\Create;
use App\Livewire\UserManagement\Edit;
use App\Livewire\UserManagement\Index;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'mabes'])->group(function () {
    Route::livewire('user', Index::class)->name('user.index');
    Route::livewire('user/create', Create::class)->name('user.create');
    Route::livewire('user/{user}/edit', Edit::class)->name('user.edit');
});
