<?php

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\ProposalChapter;
use App\Livewire\GrantDetail\Show;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use App\Repositories\GrantReviewRepository;
use App\Repositories\MabesGrantReviewRepository;
use Livewire\Livewire;

function createSatkerUserForDetail(): User
{
    $polda = User::factory()->create();
    $polda->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $polda->id,
        ])
    );

    return $user;
}

function createPoldaUserForDetail(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createMabesUserForDetail(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createGrantWithFullData(User $satkerUser): Grant
{
    $donor = Donor::factory()->create();

    $grant = $satkerUser->unit->grants()->create(
        Grant::factory()->planned()->raw([
            'id_pemberi_hibah' => $donor->id,
            'nilai_hibah' => 100000000,
            'mata_uang' => 'IDR',
        ])
    );

    // Status history
    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Grant initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::PlanningInitialized->value,
        'status_sesudah' => GrantStatus::FillingDonorCandidate->value,
        'keterangan' => 'Filling donor candidate data',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::FillingDonorCandidate->value,
        'status_sesudah' => GrantStatus::CreatingProposalDocument->value,
        'keterangan' => 'Creating proposal document',
    ]);

    // Proposal chapters
    $chapter = $grant->information()->create([
        'judul' => ProposalChapter::General->value,
        'tahapan' => GrantStage::Planning->value,
    ]);
    $chapter->contents()->create([
        'subjudul' => 'Latar Belakang',
        'isi' => 'Latar belakang kegiatan ini adalah...',
        'nomor_urut' => 1,
    ]);

    $purposeChapter = $grant->information()->create([
        'judul' => ProposalChapter::Purpose->value,
        'tahapan' => GrantStage::Planning->value,
    ]);
    $purposeChapter->contents()->create([
        'subjudul' => 'Maksud',
        'isi' => 'Maksud dari kegiatan ini adalah...',
        'nomor_urut' => 1,
    ]);

    // Budget plans
    $grant->budgetPlans()->create([
        'nomor_urut' => 1,
        'uraian' => 'Peralatan',
        'nilai' => 50000000,
    ]);
    $grant->budgetPlans()->create([
        'nomor_urut' => 2,
        'uraian' => 'Operasional',
        'nilai' => 30000000,
    ]);

    // Activity schedules
    $grant->activitySchedules()->create([
        'uraian_kegiatan' => 'Pengadaan peralatan',
        'tanggal_mulai' => '2026-03-01',
        'tanggal_selesai' => '2026-06-30',
    ]);

    // Assessment (Satker)
    $assessmentHistory = $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::CreatingProposalDocument->value,
        'status_sesudah' => GrantStatus::CreatingPlanningAssessment->value,
        'keterangan' => 'Creating assessment',
    ]);

    foreach (AssessmentAspect::cases() as $aspect) {
        $assessment = $assessmentHistory->assessments()->create([
            'judul' => $aspect->label(),
            'aspek' => $aspect->value,
            'tahapan' => GrantStage::Planning->value,
        ]);
        $assessment->contents()->create([
            'subjudul' => $aspect->label(),
            'isi' => "Kajian aspek {$aspect->label()} oleh satker",
            'nomor_urut' => 1,
        ]);
    }

    return $grant;
}

function submitAndReviewGrant(Grant $grant, User $poldaUser): void
{
    // Submit to Polda
    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::CreatingPlanningAssessment->value,
        'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
        'keterangan' => 'Submitted to Polda',
    ]);

    // Polda review
    $poldaRepo = app(GrantReviewRepository::class);
    $poldaRepo->startReview($grant, $poldaUser->unit);

    $assessments = $poldaRepo->getReviewAssessments($grant);
    foreach ($assessments as $assessment) {
        $poldaRepo->submitAspectResult($assessment, $poldaUser->unit, AssessmentResult::Fulfilled, null);
    }
}

function submitAndReviewGrantByMabes(Grant $grant, User $poldaUser, User $mabesUser): void
{
    submitAndReviewGrant($grant, $poldaUser);

    // Mabes review
    $mabesRepo = app(MabesGrantReviewRepository::class);
    $mabesRepo->startReview($grant, $mabesUser->unit);

    $assessments = $mabesRepo->getReviewAssessments($grant);
    foreach ($assessments as $assessment) {
        $mabesRepo->submitAspectResult($assessment, $mabesUser->unit, AssessmentResult::Fulfilled, 'Terpenuhi');
    }
}

// -------------------------------------------------------------------
// Access Control
// -------------------------------------------------------------------

describe('Grant Detail — Access Control', function () {
    it('allows Satker to view their own grant', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker)
            ->get(route('grant-detail.show', $grant))
            ->assertSuccessful();
    });

    it('denies Satker from viewing another unit grant', function () {
        $satker = createSatkerUserForDetail();
        $otherSatker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($otherSatker);

        $this->actingAs($satker)
            ->get(route('grant-detail.show', $grant))
            ->assertForbidden();
    });

    it('allows Polda to view child Satker grant', function () {
        $satker = createSatkerUserForDetail();
        $polda = User::find($satker->unit->id_unit_atasan);

        $grant = createGrantWithFullData($satker);

        $this->actingAs($polda)
            ->get(route('grant-detail.show', $grant))
            ->assertSuccessful();
    });

    it('denies Polda from viewing unrelated Satker grant', function () {
        $polda = createPoldaUserForDetail();
        $otherSatker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($otherSatker);

        $this->actingAs($polda)
            ->get(route('grant-detail.show', $grant))
            ->assertForbidden();
    });

    it('allows Mabes to view any grant', function () {
        $mabes = createMabesUserForDetail();
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($mabes)
            ->get(route('grant-detail.show', $grant))
            ->assertSuccessful();
    });

    it('redirects unauthenticated users', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->get(route('grant-detail.show', $grant))
            ->assertRedirect(route('login'));
    });
});

// -------------------------------------------------------------------
// Tab 1 — Grant Info
// -------------------------------------------------------------------

describe('Grant Detail — Tab: Grant Info', function () {
    it('shows grant overview information', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText($grant->nama_hibah)
            ->assertSeeText($satker->unit->nama_unit)
            ->assertSeeText('100.000.000');
    });

    it('shows donor information', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText($grant->donor->nama);
    });

    it('shows status timeline', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText('Grant initialized')
            ->assertSeeText('Filling donor candidate data');
    });

    it('shows planning number when issued', function () {
        $satker = createSatkerUserForDetail();
        $polda = User::find($satker->unit->id_unit_atasan);
        $mabes = createMabesUserForDetail();
        $grant = createGrantWithFullData($satker);

        submitAndReviewGrantByMabes($grant, $polda, $mabes);

        $grant->refresh();
        $planningNumber = $grant->numberings->where('tahapan', GrantStage::Planning)->first()?->nomor;

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText($planningNumber);
    });
});

// -------------------------------------------------------------------
// Tab 2 — Proposal Info
// -------------------------------------------------------------------

describe('Grant Detail — Tab: Proposal Info', function () {
    it('shows proposal chapters', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'proposal-info')
            ->assertSeeText(ProposalChapter::General->label())
            ->assertSeeText('Latar belakang kegiatan ini adalah...')
            ->assertSeeText(ProposalChapter::Purpose->label());
    });

    it('shows budget table', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'proposal-info')
            ->assertSeeText('Peralatan')
            ->assertSeeText('50.000.000')
            ->assertSeeText('Operasional')
            ->assertSeeText('30.000.000')
            ->assertSeeText('80.000.000');
    });

    it('shows schedule table', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'proposal-info')
            ->assertSeeText('Pengadaan peralatan');
    });

    it('shows empty state when no proposal data', function () {
        $satker = createSatkerUserForDetail();

        $grant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw()
        );
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
            'keterangan' => 'Grant initialized',
        ]);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'proposal-info')
            ->assertSeeText(__('page.grant-detail.no-proposal-data'));
    });
});

// -------------------------------------------------------------------
// Tab 3 — Assessment Info
// -------------------------------------------------------------------

describe('Grant Detail — Tab: Assessment Info', function () {
    it('shows satker assessment content', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'assessment-info')
            ->assertSeeText(AssessmentAspect::Technical->label())
            ->assertSeeText('Kajian aspek Teknis oleh satker')
            ->assertSeeText(AssessmentAspect::Economic->label());
    });

    it('shows polda assessment results', function () {
        $satker = createSatkerUserForDetail();
        $polda = User::find($satker->unit->id_unit_atasan);
        $grant = createGrantWithFullData($satker);

        submitAndReviewGrant($grant, $polda);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'assessment-info')
            ->assertSeeText(__('page.grant-review.result-fulfilled'));
    });

    it('shows mabes assessment results', function () {
        $satker = createSatkerUserForDetail();
        $polda = User::find($satker->unit->id_unit_atasan);
        $mabes = createMabesUserForDetail();
        $grant = createGrantWithFullData($satker);

        submitAndReviewGrantByMabes($grant, $polda, $mabes);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'assessment-info')
            ->assertSeeText(__('page.grant-detail.mabes-result'))
            ->assertSeeText('Terpenuhi');
    });

    it('shows empty state when no assessment data', function () {
        $satker = createSatkerUserForDetail();

        $grant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw()
        );
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
            'keterangan' => 'Grant initialized',
        ]);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'assessment-info')
            ->assertSeeText(__('page.grant-detail.no-assessment-data'));
    });
});

// -------------------------------------------------------------------
// Tab Navigation
// -------------------------------------------------------------------

describe('Grant Detail — Tab Navigation', function () {
    it('defaults to grant-info tab', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSet('activeTab', 'grant-info')
            ->assertSeeText(__('page.grant-detail.grant-overview'));
    });

    it('switches to proposal-info tab', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'proposal-info')
            ->assertSet('activeTab', 'proposal-info')
            ->assertSeeText(ProposalChapter::General->label());
    });

    it('switches to assessment-info tab', function () {
        $satker = createSatkerUserForDetail();
        $grant = createGrantWithFullData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'assessment-info')
            ->assertSet('activeTab', 'assessment-info')
            ->assertSeeText(AssessmentAspect::Technical->label());
    });
});
