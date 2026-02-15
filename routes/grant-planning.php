<?php

use App\Livewire\GrantPlanning\Assessment;
use App\Livewire\GrantPlanning\DonorInfo;
use App\Livewire\GrantPlanning\Index;
use App\Livewire\GrantPlanning\Initialize;
use App\Livewire\GrantPlanning\ProposalDocument;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'satker'])->group(function () {
    Route::livewire('grant-planning', Index::class)->name('grant-planning.index');
    Route::livewire('grant-planning/create', Initialize::class)->name('grant-planning.create');
    Route::livewire('grant-planning/{grant}/edit', Initialize::class)->name('grant-planning.edit');
    Route::livewire('grant-planning/{grant}/donor', DonorInfo::class)->name('grant-planning.donor');
    Route::livewire('grant-planning/{grant}/proposal-document', ProposalDocument::class)->name('grant-planning.proposal-document');
    Route::livewire('grant-planning/{grant}/assessment', Assessment::class)->name('grant-planning.assessment');
});
