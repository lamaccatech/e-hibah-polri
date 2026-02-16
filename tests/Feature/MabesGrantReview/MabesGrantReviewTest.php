<?php

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStatus;
use App\Livewire\MabesGrantReview\Index;
use App\Livewire\MabesGrantReview\Review;
use App\Models\GrantAssessment;
use App\Models\OrgUnit;
use App\Models\User;
use App\Repositories\GrantReviewRepository;
use App\Repositories\MabesGrantReviewRepository;
use Livewire\Livewire;

function createMabesUserForReviewTest(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createPoldaVerifiedGrant(): \App\Models\Grant
{
    $mabes = createMabesUserForReviewTest();

    $poldaUser = User::factory()->create();
    $poldaUser->unit()->create(OrgUnit::factory()->satuanInduk()->raw([
        'id_unit_atasan' => $mabes->id,
    ]));

    $satkerUser = User::factory()->create();
    $satkerUser->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
        'id_unit_atasan' => $poldaUser->id,
    ]));

    $grant = $satkerUser->unit->grants()->create(
        \App\Models\Grant::factory()->planned()->raw()
    );

    // Build status chain: Initialized → Submitted → PoldaReviewing
    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Grant initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::PlanningInitialized->value,
        'status_sesudah' => GrantStatus::CreatingPlanningAssessment->value,
        'keterangan' => 'Creating assessment',
    ]);

    // Create Satker assessment records
    $assessmentHistory = $grant->statusHistory()->latest('id')->first();
    foreach (AssessmentAspect::cases() as $aspect) {
        $assessmentHistory->assessments()->create([
            'judul' => $aspect->label(),
            'aspek' => $aspect->value,
            'tahapan' => \App\Enums\GrantStage::Planning->value,
        ]);
    }

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::CreatingPlanningAssessment->value,
        'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
        'keterangan' => 'Submitted to Polda',
    ]);

    // Start Polda review and approve all aspects
    $poldaRepository = app(GrantReviewRepository::class);
    $poldaRepository->startReview($grant, $poldaUser->unit);

    $poldaAssessments = $poldaRepository->getReviewAssessments($grant);
    foreach ($poldaAssessments as $assessment) {
        $poldaRepository->submitAspectResult($assessment, $poldaUser->unit, AssessmentResult::Fulfilled, null);
    }

    return $grant;
}

function startMabesReviewForGrant(\App\Models\Grant $grant): void
{
    $repository = app(MabesGrantReviewRepository::class);
    // Find a Mabes unit to use
    $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
    $repository->startReview($grant, $mabesUnit);
}

describe('Mabes Grant Review — Access', function () {
    it('allows Mabes to access grant review index', function () {
        $mabes = createMabesUserForReviewTest();

        $this->actingAs($mabes)
            ->get(route('mabes-grant-review.index'))
            ->assertSuccessful();
    });

    it('redirects non-Mabes users from grant review index', function () {
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

        $this->actingAs($satker)
            ->get(route('mabes-grant-review.index'))
            ->assertRedirect(route('dashboard'));
    });

    it('redirects Polda from grant review index', function () {
        $polda = User::factory()->create();
        $polda->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

        $this->actingAs($polda)
            ->get(route('mabes-grant-review.index'))
            ->assertRedirect(route('dashboard'));
    });
});

describe('Mabes Grant Review — Listing', function () {
    it('shows Polda-verified grants in the list', function () {
        $grant = createPoldaVerifiedGrant();

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText($grant->nama_hibah);
    });

    it('shows empty state when no grants are available', function () {
        $mabes = createMabesUserForReviewTest();

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText(__('page.mabes-grant-review.empty-state'));
    });
});

describe('Mabes Grant Review — Start Review', function () {
    it('allows Mabes to start reviewing a verified grant', function () {
        $grant = createPoldaVerifiedGrant();

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->assertSet('showStartReviewModal', true)
            ->assertSet('grantToReviewId', $grant->id)
            ->call('startReview');

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::MabesReviewingPlanning);
    });

    it('creates 4 assessment records when starting review', function () {
        $grant = createPoldaVerifiedGrant();

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview');

        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::MabesReviewingPlanning)
            ->first();

        $assessments = $reviewingHistory->assessments;
        expect($assessments)->toHaveCount(4);

        $aspects = $assessments->pluck('aspek')->all();
        expect($aspects)->toContain(AssessmentAspect::Technical);
        expect($aspects)->toContain(AssessmentAspect::Economic);
        expect($aspects)->toContain(AssessmentAspect::Political);
        expect($aspects)->toContain(AssessmentAspect::Strategic);
    });

    it('prevents starting review on non-reviewable grants', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview')
            ->assertForbidden();
    });
});

describe('Mabes Grant Review — Per-Aspect Assessment', function () {
    it('allows Mabes to approve an aspect', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id)
                ->where('status_sesudah', GrantStatus::MabesReviewingPlanning))
            ->where('aspek', AssessmentAspect::Technical)
            ->first();

        Livewire::test(Review::class, ['grant' => $grant])
            ->call('openResultModal', $assessment->id, $assessment->aspek->label())
            ->assertSet('showResultModal', true)
            ->set('result', AssessmentResult::Fulfilled->value)
            ->call('submitResult');

        $assessment->refresh();
        expect($assessment->result)->not->toBeNull();
        expect($assessment->result->rekomendasi)->toBe(AssessmentResult::Fulfilled);
    });

    it('allows Mabes to reject an aspect with keterangan', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id)
                ->where('status_sesudah', GrantStatus::MabesReviewingPlanning))
            ->where('aspek', AssessmentAspect::Technical)
            ->first();

        Livewire::test(Review::class, ['grant' => $grant])
            ->call('openResultModal', $assessment->id, $assessment->aspek->label())
            ->set('result', AssessmentResult::Rejected->value)
            ->set('remarks', 'Tidak memenuhi standar')
            ->call('submitResult');

        $assessment->refresh();
        expect($assessment->result->rekomendasi)->toBe(AssessmentResult::Rejected);
        expect($assessment->result->keterangan)->toBe('Tidak memenuhi standar');
    });

    it('allows Mabes to request revision with keterangan', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id)
                ->where('status_sesudah', GrantStatus::MabesReviewingPlanning))
            ->where('aspek', AssessmentAspect::Economic)
            ->first();

        Livewire::test(Review::class, ['grant' => $grant])
            ->call('openResultModal', $assessment->id, $assessment->aspek->label())
            ->set('result', AssessmentResult::Revision->value)
            ->set('remarks', 'Perlu perbaikan data anggaran')
            ->call('submitResult');

        $assessment->refresh();
        expect($assessment->result->rekomendasi)->toBe(AssessmentResult::Revision);
        expect($assessment->result->keterangan)->toBe('Perlu perbaikan data anggaran');
    });
});

describe('Mabes Grant Review — Rejection Notification', function () {
    it('notifies Satker when Mabes rejects grant', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesGrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Rejected, 'Ditolak');
            } else {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
            }
        }

        $satkerUser = $grant->orgUnit->user;
        $notification = $satkerUser->notifications()->latest()->first();

        expect($notification)->not->toBeNull();
        expect($notification->data['grant_id'])->toBe($grant->id);
        expect($notification->data['grant_name'])->toBe($grant->nama_hibah);
        expect($notification->data['rejected_by'])->toBe('Mabes');
    });
});

describe('Mabes Grant Review — Revision Cycle', function () {
    it('full cycle: Mabes requests revision → Satker resubmits → Polda re-reviews → approves → Mabes re-reviews', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $mabesRepository = app(MabesGrantReviewRepository::class);

        // Mabes requests revision
        $assessments = $mabesRepository->getReviewAssessments($grant);
        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $mabesRepository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $mabesRepository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
            }
        }

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::MabesRequestedPlanningRevision);

        // Satker resubmits revision
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::MabesRequestedPlanningRevision->value,
            'status_sesudah' => GrantStatus::PlanningRevisionResubmitted->value,
            'keterangan' => 'Satker mengajukan revisi',
        ]);

        // Polda re-reviews and approves
        $poldaUnit = $grant->orgUnit->parent;
        $poldaRepository = app(GrantReviewRepository::class);

        expect($poldaRepository->canStartReview($grant))->toBeTrue();

        $poldaRepository->startReview($grant, $poldaUnit);
        $poldaAssessments = $poldaRepository->getReviewAssessments($grant);

        foreach ($poldaAssessments as $assessment) {
            $poldaRepository->submitAspectResult($assessment, $poldaUnit, AssessmentResult::Fulfilled, null);
        }

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::PoldaVerifiedPlanning);

        // Mabes can start re-review
        expect($mabesRepository->canStartReview($grant))->toBeTrue();

        $mabesRepository->startReview($grant, $mabesUnit);
        $newAssessments = $mabesRepository->getReviewAssessments($grant);

        // Mabes approves all
        foreach ($newAssessments as $assessment) {
            $mabesRepository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
        }

        // Should end with PlanningNumberIssued
        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::PlanningNumberIssued);
    });

    it('notifies Satker when Mabes requests revision', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesGrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
            }
        }

        $satkerUser = $grant->orgUnit->user;
        $notification = $satkerUser->notifications()->latest()->first();

        expect($notification)->not->toBeNull();
        expect($notification->data['grant_id'])->toBe($grant->id);
        expect($notification->data['grant_name'])->toBe($grant->nama_hibah);
        expect($notification->data['revision_requested_by'])->toBe('Mabes');
    });
});

describe('Mabes Grant Review — Auto-Status Resolution', function () {
    it('auto-approves grant and issues planning number when all aspects fulfilled', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesGrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $assessment) {
            $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
        }

        // Verify MabesVerifiedPlanning status exists
        $this->assertDatabaseHas('riwayat_perubahan_status_hibah', [
            'id_hibah' => $grant->id,
            'status_sebelum' => GrantStatus::MabesReviewingPlanning->value,
            'status_sesudah' => GrantStatus::MabesVerifiedPlanning->value,
        ]);

        // Verify PlanningNumberIssued status
        $this->assertDatabaseHas('riwayat_perubahan_status_hibah', [
            'id_hibah' => $grant->id,
            'status_sebelum' => GrantStatus::MabesVerifiedPlanning->value,
            'status_sesudah' => GrantStatus::PlanningNumberIssued->value,
        ]);

        // Verify numbering record
        $this->assertDatabaseHas('penomoran_hibah', [
            'id_hibah' => $grant->id,
            'tahapan' => \App\Enums\GrantStage::Planning->value,
        ]);

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PlanningNumberIssued);
    });

    it('notifies Satker with planning number when all aspects fulfilled', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesGrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $assessment) {
            $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
        }

        $satkerUser = $grant->orgUnit->user;
        $notification = $satkerUser->notifications()->latest()->first();

        expect($notification)->not->toBeNull();
        expect($notification->data['grant_id'])->toBe($grant->id);
        expect($notification->data['grant_name'])->toBe($grant->nama_hibah);
        expect($notification->data['planning_number'])->not->toBeNull();
        expect($notification->data['planning_number'])->toContain('SUHL');
    });

    it('auto-rejects grant when any aspect rejected', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesGrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Rejected, 'Ditolak');
            } else {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::MabesRejectedPlanning);
    });

    it('auto-requests revision when any aspect has revision and none rejected', function () {
        $grant = createPoldaVerifiedGrant();
        startMabesReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesGrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::MabesRequestedPlanningRevision);
    });
});
