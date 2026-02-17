<?php

use App\Http\Controllers\DownloadDocumentController;
use App\Http\Controllers\DownloadStatusHistoryFileController;
use App\Livewire\GrantDocument\Generate;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('grant-detail/{grant}/document/{type}', Generate::class)->name('grant-document.generate');
    Route::get('grant-detail/{grant}/document/{grantDocument}/download', DownloadDocumentController::class)->name('grant-document.download');
    Route::get('grant-detail/{grant}/file/{file}/download', DownloadStatusHistoryFileController::class)->name('grant-file.download');
});
