<?php

// SPEC: Post-Approval Flow — Upload Signed Agreement + SEHATI Submission
// See specs/features/post-approval.md for full feature spec.

use App\Enums\FileType;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\GrantType;
use App\Livewire\GrantAgreement\SehatiSubmission;
use App\Livewire\GrantAgreement\UploadSignedAgreement;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function createSatkerUserForPostApproval(): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw()
    );

    return $user;
}

function createGrantWithAgreementNumberIssued(User $user): Grant
{
    $donor = Donor::factory()->create();
    $grant = $user->unit->grants()->create(
        Grant::factory()->raw([
            'jenis_hibah' => GrantType::Direct->value,
            'tahapan' => GrantStage::Agreement->value,
            'ada_usulan' => false,
            'id_pemberi_hibah' => $donor->id,
        ])
    );

    // Build status history chain ending at AgreementNumberIssued
    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::FillingReceptionData->value,
        'keterangan' => 'Agreement initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::FillingReceptionData->value,
        'status_sesudah' => GrantStatus::AgreementSubmittedToPolda->value,
        'keterangan' => 'Submitted to Polda',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::AgreementSubmittedToPolda->value,
        'status_sesudah' => GrantStatus::AgreementNumberIssued->value,
        'keterangan' => 'Agreement number issued by Mabes',
    ]);

    return $grant;
}

// ============================================================
// Upload Signed Agreement — Happy Path
// ============================================================

describe('Upload Signed Agreement', function () {
    it('allows Satker to upload signed agreement when status is AgreementNumberIssued', function () {
        Storage::fake();
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        Livewire::actingAs($user)
            ->test(UploadSignedAgreement::class, ['grant' => $grant])
            ->set('signedAgreementFile', UploadedFile::fake()->create('naskah-perjanjian.pdf', 5000, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('grant-agreement.sehati', $grant));

        $latestHistory = $grant->statusHistory()->latest('id')->first();
        expect($latestHistory->status_sesudah)->toBe(GrantStatus::UploadingSignedAgreement)
            ->and($latestHistory->status_sebelum)->toBe(GrantStatus::AgreementNumberIssued);

        // Check file was attached
        $file = $latestHistory->files()->first();
        expect($file)->not->toBeNull()
            ->and($file->file_type)->toBe(FileType::Agreement);
    });

    it('requires a PDF file', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        Livewire::actingAs($user)
            ->test(UploadSignedAgreement::class, ['grant' => $grant])
            ->call('save')
            ->assertHasErrors(['signedAgreementFile']);
    });

    it('rejects non-PDF files', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        Livewire::actingAs($user)
            ->test(UploadSignedAgreement::class, ['grant' => $grant])
            ->set('signedAgreementFile', UploadedFile::fake()->create('naskah.docx', 1000, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'))
            ->call('save')
            ->assertHasErrors(['signedAgreementFile']);
    });

    it('rejects signed agreement files exceeding 20MB', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        Livewire::actingAs($user)
            ->test(UploadSignedAgreement::class, ['grant' => $grant])
            ->set('signedAgreementFile', UploadedFile::fake()->create('naskah.pdf', 20481, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['signedAgreementFile']);
    });
});

// ============================================================
// SEHATI Submission — Happy Path
// ============================================================

describe('SEHATI Submission', function () {
    it('allows Satker to fill SEHATI data when status is UploadingSignedAgreement', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        // Advance to UploadingSignedAgreement
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::AgreementNumberIssued->value,
            'status_sesudah' => GrantStatus::UploadingSignedAgreement->value,
            'keterangan' => 'Signed agreement uploaded',
        ]);

        Livewire::actingAs($user)
            ->test(SehatiSubmission::class, ['grant' => $grant])
            ->set('grantRecipient', 'Polres Jakarta Selatan')
            ->set('fundingSource', 'APBN')
            ->set('fundingType', 'Hibah Langsung')
            ->set('withdrawalMethod', 'Letter of Credit')
            ->set('effectiveDate', '2026-01-01')
            ->set('withdrawalDeadline', '2026-06-30')
            ->set('accountClosingDate', '2026-12-31')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('grant-detail.show', $grant));

        // Check status history
        $latestHistory = $grant->statusHistory()->latest('id')->first();
        expect($latestHistory->status_sesudah)->toBe(GrantStatus::SubmittingToFinanceMinistry)
            ->and($latestHistory->status_sebelum)->toBe(GrantStatus::UploadingSignedAgreement);

        // Check GrantFinanceMinistrySubmission record created
        $grant->refresh();
        $submission = $grant->financeMinistrySubmission;
        expect($submission)->not->toBeNull()
            ->and($submission->penerima_hibah)->toBe('Polres Jakarta Selatan')
            ->and($submission->sumber_pembiayaan)->toBe('APBN')
            ->and($submission->jenis_pembiayaan)->toBe('Hibah Langsung')
            ->and($submission->cara_penarikan)->toBe('Letter of Credit')
            ->and($submission->tanggal_efektif->format('Y-m-d'))->toBe('2026-01-01')
            ->and($submission->tanggal_batas_penarikan->format('Y-m-d'))->toBe('2026-06-30')
            ->and($submission->tanggal_penutupan_rekening->format('Y-m-d'))->toBe('2026-12-31');
    });

    it('fails with missing required fields', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::AgreementNumberIssued->value,
            'status_sesudah' => GrantStatus::UploadingSignedAgreement->value,
            'keterangan' => 'Signed agreement uploaded',
        ]);

        Livewire::actingAs($user)
            ->test(SehatiSubmission::class, ['grant' => $grant])
            ->call('save')
            ->assertHasErrors([
                'grantRecipient',
                'fundingSource',
                'fundingType',
                'withdrawalMethod',
                'effectiveDate',
                'withdrawalDeadline',
                'accountClosingDate',
            ]);
    });

    it('fails when withdrawalDeadline is before effectiveDate', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::AgreementNumberIssued->value,
            'status_sesudah' => GrantStatus::UploadingSignedAgreement->value,
            'keterangan' => 'Signed agreement uploaded',
        ]);

        Livewire::actingAs($user)
            ->test(SehatiSubmission::class, ['grant' => $grant])
            ->set('grantRecipient', 'Test')
            ->set('fundingSource', 'Test')
            ->set('fundingType', 'Test')
            ->set('withdrawalMethod', 'Test')
            ->set('effectiveDate', '2026-06-01')
            ->set('withdrawalDeadline', '2026-01-01')
            ->set('accountClosingDate', '2026-12-31')
            ->call('save')
            ->assertHasErrors(['withdrawalDeadline']);
    });

    it('fails when accountClosingDate is before withdrawalDeadline', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::AgreementNumberIssued->value,
            'status_sesudah' => GrantStatus::UploadingSignedAgreement->value,
            'keterangan' => 'Signed agreement uploaded',
        ]);

        Livewire::actingAs($user)
            ->test(SehatiSubmission::class, ['grant' => $grant])
            ->set('grantRecipient', 'Test')
            ->set('fundingSource', 'Test')
            ->set('fundingType', 'Test')
            ->set('withdrawalMethod', 'Test')
            ->set('effectiveDate', '2026-01-01')
            ->set('withdrawalDeadline', '2026-06-30')
            ->set('accountClosingDate', '2026-03-01')
            ->call('save')
            ->assertHasErrors(['accountClosingDate']);
    });
});

// ============================================================
// Access Control
// ============================================================

describe('Post-Approval Access Control', function () {
    it('returns 403 for non-owner Satker on upload signed agreement', function () {
        $owner = createSatkerUserForPostApproval();
        $otherUser = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($owner);

        Livewire::actingAs($otherUser)
            ->test(UploadSignedAgreement::class, ['grant' => $grant])
            ->assertForbidden();
    });

    it('returns 403 for non-owner Satker on SEHATI submission', function () {
        $owner = createSatkerUserForPostApproval();
        $otherUser = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($owner);

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::AgreementNumberIssued->value,
            'status_sesudah' => GrantStatus::UploadingSignedAgreement->value,
            'keterangan' => 'Signed agreement uploaded',
        ]);

        Livewire::actingAs($otherUser)
            ->test(SehatiSubmission::class, ['grant' => $grant])
            ->assertForbidden();
    });

    it('returns 403 when uploading signed agreement with wrong status', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        // Override status to something other than AgreementNumberIssued
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::AgreementNumberIssued->value,
            'status_sesudah' => GrantStatus::UploadingSignedAgreement->value,
            'keterangan' => 'Already uploaded',
        ]);

        Livewire::actingAs($user)
            ->test(UploadSignedAgreement::class, ['grant' => $grant])
            ->assertForbidden();
    });

    it('returns 403 when submitting SEHATI with wrong status', function () {
        $user = createSatkerUserForPostApproval();
        $grant = createGrantWithAgreementNumberIssued($user);

        // Status is still AgreementNumberIssued (not UploadingSignedAgreement)
        Livewire::actingAs($user)
            ->test(SehatiSubmission::class, ['grant' => $grant])
            ->assertForbidden();
    });
});
