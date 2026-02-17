<?php

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Livewire\MabesAgreementReview\Index;
use App\Livewire\MabesAgreementReview\Review;
use App\Models\Grant;
use App\Models\GrantAssessment;
use App\Models\OrgUnit;
use App\Models\User;
use App\Repositories\AgreementReviewRepository;
use App\Repositories\MabesAgreementReviewRepository;
use Livewire\Livewire;

function createMabesUserForAgreementReviewTest(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createPoldaVerifiedAgreement(): Grant
{
    $mabes = createMabesUserForAgreementReviewTest();

    $poldaUser = User::factory()->create();
    $poldaUser->unit()->create(OrgUnit::factory()->satuanInduk()->raw([
        'id_unit_atasan' => $mabes->id,
    ]));

    $satkerUser = User::factory()->create();
    $satkerUser->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
        'id_unit_atasan' => $poldaUser->id,
    ]));

    $grant = $satkerUser->unit->grants()->create(
        Grant::factory()->directAgreement()->raw()
    );

    // Build status chain: Initialized → Assessment → Submitted
    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::FillingReceptionData->value,
        'keterangan' => 'Agreement initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::FillingReceptionData->value,
        'status_sesudah' => GrantStatus::CreatingAgreementAssessment->value,
        'keterangan' => 'Creating assessment',
    ]);

    // Create Satker assessment records
    $assessmentHistory = $grant->statusHistory()->latest('id')->first();
    foreach (AssessmentAspect::cases() as $aspect) {
        $assessmentHistory->assessments()->create([
            'judul' => $aspect->label(),
            'aspek' => $aspect->value,
            'tahapan' => GrantStage::Agreement->value,
        ]);
    }

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::CreatingAgreementAssessment->value,
        'status_sesudah' => GrantStatus::AgreementSubmittedToPolda->value,
        'keterangan' => 'Submitted to Polda',
    ]);

    // Start Polda review and approve all aspects
    $poldaRepository = app(AgreementReviewRepository::class);
    $poldaRepository->startReview($grant, $poldaUser->unit);

    $poldaAssessments = $poldaRepository->getReviewAssessments($grant);
    foreach ($poldaAssessments as $assessment) {
        $poldaRepository->submitAspectResult($assessment, $poldaUser->unit, AssessmentResult::Fulfilled, null);
    }

    return $grant;
}

function startMabesAgreementReviewForGrant(Grant $grant): void
{
    $repository = app(MabesAgreementReviewRepository::class);
    $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
    $repository->startReview($grant, $mabesUnit);
}

describe('Mabes Agreement Review — Access', function () {
    it('allows Mabes to access agreement review index', function () {
        $mabes = createMabesUserForAgreementReviewTest();

        $this->actingAs($mabes)
            ->get(route('mabes-agreement-review.index'))
            ->assertSuccessful();
    });

    it('redirects non-Mabes users from agreement review index', function () {
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

        $this->actingAs($satker)
            ->get(route('mabes-agreement-review.index'))
            ->assertRedirect(route('dashboard'));
    });

    it('redirects Polda from agreement review index', function () {
        $polda = User::factory()->create();
        $polda->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

        $this->actingAs($polda)
            ->get(route('mabes-agreement-review.index'))
            ->assertRedirect(route('dashboard'));
    });
});

describe('Mabes Agreement Review — Listing', function () {
    it('shows Polda-verified agreements in the list', function () {
        $grant = createPoldaVerifiedAgreement();

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText($grant->nama_hibah);
    });

    it('shows empty state when no agreements are available', function () {
        $mabes = createMabesUserForAgreementReviewTest();

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText(__('page.mabes-agreement-review.empty-state'));
    });
});

describe('Mabes Agreement Review — Start Review', function () {
    it('allows Mabes to start reviewing a verified agreement', function () {
        $grant = createPoldaVerifiedAgreement();

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->assertSet('showStartReviewModal', true)
            ->assertSet('grantToReviewId', $grant->id)
            ->call('startReview');

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::MabesReviewingAgreement);
    });

    it('creates 4 assessment records when starting review', function () {
        $grant = createPoldaVerifiedAgreement();

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview');

        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::MabesReviewingAgreement)
            ->first();

        $assessments = $reviewingHistory->assessments;
        expect($assessments)->toHaveCount(4);

        $aspects = $assessments->pluck('aspek')->all();
        expect($aspects)->toContain(AssessmentAspect::Technical);
        expect($aspects)->toContain(AssessmentAspect::Economic);
        expect($aspects)->toContain(AssessmentAspect::Political);
        expect($aspects)->toContain(AssessmentAspect::Strategic);
    });

    it('prevents starting review on non-reviewable agreements', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview')
            ->assertForbidden();
    });
});

describe('Mabes Agreement Review — Per-Aspect Assessment', function () {
    it('allows Mabes to approve an aspect', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id)
                ->where('status_sesudah', GrantStatus::MabesReviewingAgreement))
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
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id)
                ->where('status_sesudah', GrantStatus::MabesReviewingAgreement))
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
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id)
                ->where('status_sesudah', GrantStatus::MabesReviewingAgreement))
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

describe('Mabes Agreement Review — Rejection Notification', function () {
    it('notifies Satker when Mabes rejects agreement', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesAgreementReviewRepository::class);
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

describe('Mabes Agreement Review — Revision Cycle', function () {
    it('full cycle: Mabes requests revision → Satker resubmits → Polda re-reviews → approves → Mabes re-reviews', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $mabesRepository = app(MabesAgreementReviewRepository::class);

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
            ->toBe(GrantStatus::MabesRequestedAgreementRevision);

        // Satker resubmits revision
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::MabesRequestedAgreementRevision->value,
            'status_sesudah' => GrantStatus::AgreementRevisionResubmitted->value,
            'keterangan' => 'Satker mengajukan revisi perjanjian',
        ]);

        // Polda re-reviews and approves
        $poldaUnit = $grant->orgUnit->parent;
        $poldaRepository = app(AgreementReviewRepository::class);

        expect($poldaRepository->canStartReview($grant))->toBeTrue();

        $poldaRepository->startReview($grant, $poldaUnit);
        $poldaAssessments = $poldaRepository->getReviewAssessments($grant);

        foreach ($poldaAssessments as $assessment) {
            $poldaRepository->submitAspectResult($assessment, $poldaUnit, AssessmentResult::Fulfilled, null);
        }

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::PoldaVerifiedAgreement);

        // Mabes can start re-review
        expect($mabesRepository->canStartReview($grant))->toBeTrue();

        $mabesRepository->startReview($grant, $mabesUnit);
        $newAssessments = $mabesRepository->getReviewAssessments($grant);

        // Mabes approves all
        foreach ($newAssessments as $assessment) {
            $mabesRepository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
        }

        // Should end with AgreementNumberIssued
        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::AgreementNumberIssued);
    });

    it('notifies Satker when Mabes requests revision', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesAgreementReviewRepository::class);
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

describe('Mabes Agreement Review — Auto-Status Resolution', function () {
    it('auto-approves agreement and issues agreement number when all aspects fulfilled', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesAgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $assessment) {
            $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
        }

        // Verify MabesVerifiedAgreement status exists
        $this->assertDatabaseHas('riwayat_perubahan_status_hibah', [
            'id_hibah' => $grant->id,
            'status_sebelum' => GrantStatus::MabesReviewingAgreement->value,
            'status_sesudah' => GrantStatus::MabesVerifiedAgreement->value,
        ]);

        // Verify AgreementNumberIssued status
        $this->assertDatabaseHas('riwayat_perubahan_status_hibah', [
            'id_hibah' => $grant->id,
            'status_sebelum' => GrantStatus::MabesVerifiedAgreement->value,
            'status_sesudah' => GrantStatus::AgreementNumberIssued->value,
        ]);

        // Verify numbering record
        $this->assertDatabaseHas('penomoran_hibah', [
            'id_hibah' => $grant->id,
            'tahapan' => GrantStage::Agreement->value,
        ]);

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::AgreementNumberIssued);
    });

    it('notifies Satker with agreement number when all aspects fulfilled', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesAgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $assessment) {
            $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
        }

        $satkerUser = $grant->orgUnit->user;
        $notification = $satkerUser->notifications()->latest()->first();

        expect($notification)->not->toBeNull();
        expect($notification->data['grant_id'])->toBe($grant->id);
        expect($notification->data['grant_name'])->toBe($grant->nama_hibah);
        expect($notification->data['agreement_number'])->not->toBeNull();
        expect($notification->data['agreement_number'])->toContain('NPH');
    });

    it('auto-rejects agreement when any aspect rejected', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesAgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Rejected, 'Ditolak');
            } else {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::MabesRejectedAgreement);
    });

    it('auto-requests revision when any aspect has revision and none rejected', function () {
        $grant = createPoldaVerifiedAgreement();
        startMabesAgreementReviewForGrant($grant);

        $mabesUnit = OrgUnit::where('level_unit', \App\Enums\UnitLevel::Mabes)->first();
        $repository = app(MabesAgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $repository->submitAspectResult($assessment, $mabesUnit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::MabesRequestedAgreementRevision);
    });
});
