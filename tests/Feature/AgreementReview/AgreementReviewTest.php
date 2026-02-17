<?php

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Livewire\AgreementReview\Index;
use App\Livewire\AgreementReview\Review;
use App\Models\Grant;
use App\Models\GrantAssessment;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createPoldaUserForAgreementReview(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createSatkerUserUnderPoldaForAgreementReview(User $poldaUser): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $poldaUser->id,
        ])
    );

    return $user;
}

function createSubmittedAgreementForReview(User $satkerUser): Grant
{
    $grant = $satkerUser->unit->grants()->create(
        Grant::factory()->directAgreement()->raw()
    );

    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::FillingReceptionData->value,
        'keterangan' => 'Agreement initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::FillingReceptionData->value,
        'status_sesudah' => GrantStatus::AgreementSubmittedToPolda->value,
        'keterangan' => 'Submitted to Polda',
    ]);

    return $grant;
}

function startAgreementReviewForGrant(Grant $grant): void
{
    $repository = app(\App\Repositories\AgreementReviewRepository::class);
    $poldaUnit = $grant->orgUnit->parent;
    $repository->startReview($grant, $poldaUnit);
}

describe('Agreement Review — Polda Access', function () {
    it('allows Polda to access agreement review index', function () {
        $polda = createPoldaUserForAgreementReview();

        $this->actingAs($polda)
            ->get(route('agreement-review.index'))
            ->assertSuccessful();
    });

    it('redirects non-Polda from agreement review index', function () {
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

        $this->actingAs($satker)
            ->get(route('agreement-review.index'))
            ->assertRedirect(route('dashboard'));
    });

    it('redirects Mabes from agreement review index', function () {
        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes)
            ->get(route('agreement-review.index'))
            ->assertRedirect(route('dashboard'));
    });
});

describe('Agreement Review — Listing', function () {
    it('shows agreements submitted by child Satker units', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertSeeText($grant->nama_hibah)
            ->assertSeeText($satker->unit->nama_unit);
    });

    it('does not show agreements from unrelated Satker units', function () {
        $polda = createPoldaUserForAgreementReview();
        $otherPolda = createPoldaUserForAgreementReview();
        $unrelatedSatker = createSatkerUserUnderPoldaForAgreementReview($otherPolda);
        $grant = createSubmittedAgreementForReview($unrelatedSatker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertDontSeeText($grant->nama_hibah);
    });

    it('does not show agreements that have not been submitted yet', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);

        $grant = $satker->unit->grants()->create(
            Grant::factory()->directAgreement()->raw()
        );
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::FillingReceptionData->value,
            'keterangan' => 'Agreement initialized',
        ]);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertDontSeeText($grant->nama_hibah);
    });

    it('shows empty state when no agreements are submitted', function () {
        $polda = createPoldaUserForAgreementReview();

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertSeeText(__('page.agreement-review.empty-state'));
    });

    it('displays status badge for submitted agreements', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        createSubmittedAgreementForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertSeeText(__('page.grant-agreement.badge-submitted'));
    });
});

describe('Agreement Review — Start Review', function () {
    it('allows Polda to start reviewing a submitted agreement', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->assertSet('showStartReviewModal', true)
            ->assertSet('grantToReviewId', $grant->id)
            ->call('startReview');

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaReviewingAgreement);
    });

    it('creates 4 assessment records when starting review', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview');

        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingAgreement)
            ->first();

        $assessments = $reviewingHistory->assessments;
        expect($assessments)->toHaveCount(4);

        $aspects = $assessments->pluck('aspek')->all();
        expect($aspects)->toContain(AssessmentAspect::Technical);
        expect($aspects)->toContain(AssessmentAspect::Economic);
        expect($aspects)->toContain(AssessmentAspect::Political);
        expect($aspects)->toContain(AssessmentAspect::Strategic);
    });

    it('creates assessment records with Agreement stage', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview');

        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingAgreement)
            ->first();

        $reviewingHistory->assessments->each(function ($assessment) {
            expect($assessment->tahapan)->toBe(GrantStage::Agreement);
        });
    });

    it('prevents starting review on non-reviewable agreements', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);

        startAgreementReviewForGrant($grant);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview')
            ->assertForbidden();
    });
});

describe('Agreement Review — Per-Aspect Assessment', function () {
    it('allows Polda to approve an aspect', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $this->actingAs($polda);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id))
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

    it('allows Polda to reject an aspect with keterangan', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $this->actingAs($polda);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id))
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

    it('allows Polda to request revision with keterangan', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $this->actingAs($polda);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id))
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

describe('Agreement Review — Rejection Notification', function () {
    it('notifies Satker when Polda rejects agreement', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $repository = app(\App\Repositories\AgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Rejected, 'Ditolak');
            } else {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
            }
        }

        $satkerUser = $grant->orgUnit->user;
        $notification = $satkerUser->notifications()->latest()->first();

        expect($notification)->not->toBeNull();
        expect($notification->data['grant_id'])->toBe($grant->id);
        expect($notification->data['grant_name'])->toBe($grant->nama_hibah);
        expect($notification->data['rejected_by'])->toBe('Polda');
    });
});

describe('Agreement Review — Revision Resubmission', function () {
    it('allows Polda to re-review after Satker resubmits revision', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);

        // First review: Polda requests revision
        $repository = app(\App\Repositories\AgreementReviewRepository::class);
        $repository->startReview($grant, $polda->unit);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
            }
        }

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::PoldaRequestedAgreementRevision);

        // Satker resubmits
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::PoldaRequestedAgreementRevision->value,
            'status_sesudah' => GrantStatus::AgreementRevisionResubmitted->value,
            'keterangan' => 'Satker mengajukan revisi perjanjian',
        ]);

        // Polda can start re-review
        expect($repository->canStartReview($grant))->toBeTrue();

        $repository->startReview($grant, $polda->unit);
        $newAssessments = $repository->getReviewAssessments($grant);

        expect($newAssessments)->toHaveCount(4);

        // Polda approves all
        foreach ($newAssessments as $assessment) {
            $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
        }

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::PoldaVerifiedAgreement);
    });

    it('notifies Satker when Polda requests revision', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $repository = app(\App\Repositories\AgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
            }
        }

        $satkerUser = $grant->orgUnit->user;
        $notification = $satkerUser->notifications()->latest()->first();

        expect($notification)->not->toBeNull();
        expect($notification->data['grant_id'])->toBe($grant->id);
        expect($notification->data['grant_name'])->toBe($grant->nama_hibah);
        expect($notification->data['revision_requested_by'])->toBe('Polda');
    });
});

describe('Agreement Review — Auto-Status Resolution', function () {
    it('auto-approves agreement when all aspects fulfilled', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $repository = app(\App\Repositories\AgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $assessment) {
            $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaVerifiedAgreement);
    });

    it('auto-rejects agreement when any aspect rejected', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $repository = app(\App\Repositories\AgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Rejected, 'Ditolak');
            } else {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaRejectedAgreement);
    });

    it('auto-requests revision when any aspect has revision and none rejected', function () {
        $polda = createPoldaUserForAgreementReview();
        $satker = createSatkerUserUnderPoldaForAgreementReview($polda);
        $grant = createSubmittedAgreementForReview($satker);
        startAgreementReviewForGrant($grant);

        $repository = app(\App\Repositories\AgreementReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaRequestedAgreementRevision);
    });
});
