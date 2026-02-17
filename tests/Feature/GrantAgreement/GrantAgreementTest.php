<?php

// SPEC: Grant Agreement — Satker Step 1 (Dasar Penerimaan)
// See specs/features/agreement-flow.md for full feature spec.

use App\Enums\FileType;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\GrantType;
use App\Enums\ProposalChapter;
use App\Livewire\GrantAgreement\Index;
use App\Livewire\GrantAgreement\ReceptionBasis;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function createSatkerUserForAgreementTest(?int $parentUserId = null): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $parentUserId,
        ])
    );

    return $user;
}

function createPlanningGrantWithNumber(User $user): Grant
{
    $grant = $user->unit->grants()->create(
        Grant::factory()->planned()->raw()
    );

    // Add planning status history
    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningNumberIssued->value,
        'keterangan' => 'Planning number issued',
    ]);

    // Add planning number
    $grant->numberings()->create([
        'nomor' => 'USL-2026-001',
        'kode' => 'USL',
        'nomor_urut' => 1,
        'bulan' => 1,
        'tahun' => 2026,
        'tahapan' => GrantStage::Planning->value,
        'kode_satuan_kerja' => $user->unit->kode,
    ]);

    // Add planning chapters (Purpose, Objective, Target, Benefit, etc.)
    $chapters = [
        ProposalChapter::Purpose,
        ProposalChapter::Objective,
        ProposalChapter::Target,
        ProposalChapter::Benefit,
        ProposalChapter::ImplementationPlan,
        ProposalChapter::ReportingPlan,
        ProposalChapter::EvaluationPlan,
    ];

    foreach ($chapters as $chapter) {
        $info = $grant->information()->create([
            'judul' => $chapter->value,
            'tahapan' => GrantStage::Planning->value,
        ]);

        if ($chapter === ProposalChapter::Objective) {
            $info->contents()->create([
                'subjudul' => 'PENINGKATAN KAPASITAS SDM',
                'isi' => '<p>Detail tujuan dari planning</p>',
                'nomor_urut' => 1,
            ]);
        } else {
            $info->contents()->create([
                'subjudul' => '',
                'isi' => "<p>Content for {$chapter->label()}</p>",
                'nomor_urut' => 1,
            ]);
        }
    }

    return $grant;
}

function createAgreementGrant(User $user, array $overrides = []): Grant
{
    $grant = $user->unit->grants()->create(
        Grant::factory()->directAgreement()->withoutDonor()->raw($overrides)
    );

    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::FillingReceptionData->value,
        'keterangan' => 'Test agreement initialized',
    ]);

    return $grant;
}

describe('Agreement Index', function () {
    it('shows agreement grants for satker', function () {
        $user = createSatkerUserForAgreementTest();
        $grant = createAgreementGrant($user, ['nama_hibah' => 'KEGIATAN PERJANJIAN TEST']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('KEGIATAN PERJANJIAN TEST')
            ->assertSuccessful();
    });

    it('shows empty state when no agreements exist', function () {
        $user = createSatkerUserForAgreementTest();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee(__('page.grant-agreement.empty-state'))
            ->assertSuccessful();
    });
});

describe('Step 1: Dasar Penerimaan — New Direct Agreement', function () {
    it('creates a new direct agreement with objectives', function () {
        Storage::fake();
        $user = createSatkerUserForAgreementTest();

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class)
            ->set('letterNumber', 'SURAT-2026-001')
            ->set('activityName', 'KEGIATAN HIBAH LANGSUNG')
            ->set('donorLetter', UploadedFile::fake()->create('surat.pdf', 1024, 'application/pdf'))
            ->set('objectives.0.purpose', 'PENINGKATAN KAPASITAS SDM')
            ->set('objectives.0.detail', '<p>Detail tujuan kegiatan hibah langsung ini</p>')
            ->call('save')
            ->assertRedirect();

        $grant = Grant::where('nama_hibah', 'KEGIATAN HIBAH LANGSUNG')->first();

        expect($grant)->not->toBeNull()
            ->and($grant->tahapan)->toBe(GrantStage::Agreement)
            ->and($grant->jenis_hibah)->toBe(GrantType::Direct)
            ->and($grant->ada_usulan)->toBeFalse()
            ->and($grant->nomor_surat_dari_calon_pemberi_hibah)->toBe('SURAT-2026-001');

        // Check status history
        $statusHistory = $grant->statusHistory()->latest('id')->first();
        expect($statusHistory->status_sesudah)->toBe(GrantStatus::FillingReceptionData)
            ->and($statusHistory->status_sebelum)->toBeNull();

        // Check objectives
        $objectiveInfo = $grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->where('judul', ProposalChapter::Objective->value)
            ->first();
        expect($objectiveInfo)->not->toBeNull()
            ->and($objectiveInfo->contents)->toHaveCount(1)
            ->and($objectiveInfo->contents->first()->subjudul)->toBe('PENINGKATAN KAPASITAS SDM');

        // Check donor letter attached
        $file = $statusHistory->files()->first();
        expect($file)->not->toBeNull()
            ->and($file->file_type)->toBe(FileType::DonorLetter);
    });

    it('requires donor letter for direct agreements', function () {
        $user = createSatkerUserForAgreementTest();

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class)
            ->set('letterNumber', 'SURAT-2026-002')
            ->set('activityName', 'KEGIATAN TEST')
            ->set('objectives.0.purpose', 'PENINGKATAN KAPASITAS SDM')
            ->set('objectives.0.detail', '<p>Detail tujuan kegiatan</p>')
            ->call('save')
            ->assertHasErrors(['donorLetter']);
    });

    it('validates required fields', function () {
        $user = createSatkerUserForAgreementTest();

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class)
            ->set('activityName', '')
            ->set('letterNumber', '')
            ->call('save')
            ->assertHasErrors(['activityName', 'letterNumber']);
    });
});

describe('Step 1: Dasar Penerimaan — From Planning', function () {
    it('transitions existing grant from planning to agreement', function () {
        $user = createSatkerUserForAgreementTest();
        $planningGrant = createPlanningGrantWithNumber($user);

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class)
            ->set('letterNumber', 'USL-2026-001')
            ->assertSet('hasProposal', true)
            ->set('objectives.0.purpose', 'PENINGKATAN KAPASITAS SDM')
            ->set('objectives.0.detail', '<p>Tujuan yang diperbarui untuk perjanjian</p>')
            ->call('save')
            ->assertRedirect();

        $planningGrant->refresh();

        expect($planningGrant->tahapan)->toBe(GrantStage::Agreement)
            ->and($planningGrant->ada_usulan)->toBeTrue();

        // Check status history
        $statusHistory = $planningGrant->statusHistory()->latest('id')->first();
        expect($statusHistory->status_sesudah)->toBe(GrantStatus::FillingReceptionData)
            ->and($statusHistory->status_sebelum)->toBe(GrantStatus::PlanningNumberIssued);

        // Check objectives saved as agreement stage
        $agreementObjectives = $planningGrant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->where('judul', ProposalChapter::Objective->value)
            ->first();
        expect($agreementObjectives)->not->toBeNull();

        // Check planning data copied to agreement (Purpose chapter)
        $agreementPurpose = $planningGrant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->where('judul', ProposalChapter::Purpose->value)
            ->first();
        expect($agreementPurpose)->not->toBeNull();

        // Check all 6 chapters copied from planning
        $copiedChapters = [
            ProposalChapter::Purpose,
            ProposalChapter::Target,
            ProposalChapter::Benefit,
            ProposalChapter::ImplementationPlan,
            ProposalChapter::ReportingPlan,
            ProposalChapter::EvaluationPlan,
        ];

        foreach ($copiedChapters as $chapter) {
            $agreementInfo = $planningGrant->information()
                ->where('tahapan', GrantStage::Agreement)
                ->where('judul', $chapter->value)
                ->first();
            expect($agreementInfo)->not->toBeNull("Chapter {$chapter->label()} should be copied to agreement");
        }
    });

    it('does not require donor letter when linked to planning', function () {
        $user = createSatkerUserForAgreementTest();
        createPlanningGrantWithNumber($user);

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class)
            ->set('letterNumber', 'USL-2026-001')
            ->assertSet('hasProposal', true)
            ->set('objectives.0.purpose', 'PENINGKATAN KAPASITAS SDM')
            ->set('objectives.0.detail', '<p>Tujuan yang diperbarui</p>')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();
    });

    it('pre-fills activity name from planning grant', function () {
        $user = createSatkerUserForAgreementTest();
        $planningGrant = createPlanningGrantWithNumber($user);

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class)
            ->set('letterNumber', 'USL-2026-001')
            ->assertSet('hasProposal', true)
            ->assertSet('activityName', str($planningGrant->nama_hibah)->upper()->toString());
    });
});

describe('Step 1: Dasar Penerimaan — Edit', function () {
    it('loads existing agreement data for editing', function () {
        $user = createSatkerUserForAgreementTest();
        $grant = createAgreementGrant($user, [
            'nama_hibah' => 'KEGIATAN EXISTING',
            'nomor_surat_dari_calon_pemberi_hibah' => 'SURAT-123',
        ]);

        // Add objectives
        $info = $grant->information()->create([
            'judul' => ProposalChapter::Objective->value,
            'tahapan' => GrantStage::Agreement->value,
        ]);
        $info->contents()->create([
            'subjudul' => 'PENINGKATAN KAPASITAS SDM',
            'isi' => '<p>Existing objective detail</p>',
            'nomor_urut' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class, ['grant' => $grant])
            ->assertSet('activityName', 'KEGIATAN EXISTING')
            ->assertSet('letterNumber', 'SURAT-123')
            ->assertSuccessful();
    });

    it('updates existing agreement on save', function () {
        $user = createSatkerUserForAgreementTest();
        $grant = createAgreementGrant($user, [
            'nama_hibah' => 'KEGIATAN LAMA',
            'nomor_surat_dari_calon_pemberi_hibah' => 'SURAT-OLD',
        ]);

        // Add objectives
        $info = $grant->information()->create([
            'judul' => ProposalChapter::Objective->value,
            'tahapan' => GrantStage::Agreement->value,
        ]);
        $info->contents()->create([
            'subjudul' => 'OLD PURPOSE',
            'isi' => '<p>Old detail</p>',
            'nomor_urut' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(ReceptionBasis::class, ['grant' => $grant])
            ->set('activityName', 'KEGIATAN BARU')
            ->set('letterNumber', 'SURAT-NEW')
            ->set('objectives.0.purpose', 'NEW PURPOSE')
            ->set('objectives.0.detail', '<p>New objective detail updated</p>')
            ->call('save')
            ->assertRedirect();

        $grant->refresh();
        expect($grant->nama_hibah)->toBe('KEGIATAN BARU')
            ->and($grant->nomor_surat_dari_calon_pemberi_hibah)->toBe('SURAT-NEW');

        $updatedObjective = $grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->where('judul', ProposalChapter::Objective->value)
            ->first();
        expect($updatedObjective->contents->first()->subjudul)->toBe('NEW PURPOSE');
    });
});
