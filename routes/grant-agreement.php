<?php

use App\Livewire\GrantAgreement\DonorInfo;
use App\Livewire\GrantAgreement\Index;
use App\Livewire\GrantAgreement\ReceptionBasis;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'satker'])->group(function () {
    Route::livewire('grant-agreement', Index::class)->name('grant-agreement.index');
    Route::livewire('grant-agreement/create', ReceptionBasis::class)->name('grant-agreement.create');
    Route::livewire('grant-agreement/{grant}/reception-basis', ReceptionBasis::class)->name('grant-agreement.reception-basis');
    Route::livewire('grant-agreement/{grant}/donor', DonorInfo::class)->name('grant-agreement.donor');
});
