<?php

use App\Livewire\GrantAgreement\AdditionalMaterials;
use App\Livewire\GrantAgreement\Assessment;
use App\Livewire\GrantAgreement\DonorInfo;
use App\Livewire\GrantAgreement\DraftAgreement;
use App\Livewire\GrantAgreement\Harmonization;
use App\Livewire\GrantAgreement\Index;
use App\Livewire\GrantAgreement\OtherMaterials;
use App\Livewire\GrantAgreement\ReceptionBasis;
use App\Livewire\GrantAgreement\SehatiSubmission;
use App\Livewire\GrantAgreement\UploadSignedAgreement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'satker'])->group(function () {
    Route::livewire('grant-agreement', Index::class)->name('grant-agreement.index');
    Route::livewire('grant-agreement/create', ReceptionBasis::class)->name('grant-agreement.create');
    Route::livewire('grant-agreement/{grant}/reception-basis', ReceptionBasis::class)->name('grant-agreement.reception-basis');
    Route::livewire('grant-agreement/{grant}/donor', DonorInfo::class)->name('grant-agreement.donor');
    Route::livewire('grant-agreement/{grant}/assessment', Assessment::class)->name('grant-agreement.assessment');
    Route::livewire('grant-agreement/{grant}/harmonization', Harmonization::class)->name('grant-agreement.harmonization');
    Route::livewire('grant-agreement/{grant}/additional', AdditionalMaterials::class)->name('grant-agreement.additional');
    Route::livewire('grant-agreement/{grant}/other', OtherMaterials::class)->name('grant-agreement.other');
    Route::livewire('grant-agreement/{grant}/draft', DraftAgreement::class)->name('grant-agreement.draft');
    Route::livewire('grant-agreement/{grant}/upload-signed', UploadSignedAgreement::class)->name('grant-agreement.upload-signed');
    Route::livewire('grant-agreement/{grant}/sehati', SehatiSubmission::class)->name('grant-agreement.sehati');
});
