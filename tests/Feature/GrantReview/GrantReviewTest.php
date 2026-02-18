<?php

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStatus;
use App\Livewire\GrantReview\Index;
use App\Livewire\GrantReview\Review;
use App\Models\GrantAssessment;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createPoldaUser(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createSatkerUserUnderPolda(User $poldaUser): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $poldaUser->id,
        ])
    );

    return $user;
}

function createSubmittedGrantForReview(User $satkerUser): \App\Models\Grant
{
    $grant = $satkerUser->unit->grants()->create(
        \App\Models\Grant::factory()->planned()->raw()
    );

    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Grant initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::PlanningInitialized->value,
        'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
        'keterangan' => 'Submitted to Polda',
    ]);

    return $grant;
}

function startReviewForGrant(\App\Models\Grant $grant): void
{
    $repository = app(\App\Repositories\GrantReviewRepository::class);
    $poldaUnit = $grant->orgUnit->parent;
    $repository->startReview($grant, $poldaUnit);
}

describe('Grant Review — Polda Access', function () {
    it('allows Polda to access grant review index', function () {
        $polda = createPoldaUser();

        $this->actingAs($polda)
            ->get(route('grant-review.index'))
            ->assertSuccessful();
    });

    it('redirects non-Polda from grant review index', function () {
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

        $this->actingAs($satker)
            ->get(route('grant-review.index'))
            ->assertRedirect(route('dashboard'));
    });

    it('redirects Mabes from grant review index', function () {
        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes)
            ->get(route('grant-review.index'))
            ->assertRedirect(route('dashboard'));
    });
});

describe('Grant Review — Listing', function () {
    it('shows grants submitted by child Satker units', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertSeeText($grant->nama_hibah)
            ->assertSeeText($satker->unit->nama_unit);
    });

    it('does not show grants from unrelated Satker units', function () {
        $polda = createPoldaUser();
        $otherPolda = createPoldaUser();
        $unrelatedSatker = createSatkerUserUnderPolda($otherPolda);
        $grant = createSubmittedGrantForReview($unrelatedSatker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertDontSeeText($grant->nama_hibah);
    });

    it('does not show grants that have not been submitted yet', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
            'keterangan' => 'Grant initialized',
        ]);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertDontSeeText($grant->nama_hibah);
    });

    it('shows empty state when no grants are submitted', function () {
        $polda = createPoldaUser();

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertSeeText(__('page.grant-review.empty-state'));
    });

    it('displays status badge for submitted grants', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        createSubmittedGrantForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->assertSeeText(__('page.grant-planning.badge-submitted'));
    });
});

describe('Grant Review — Start Review', function () {
    it('allows Polda to start reviewing a submitted grant', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->assertSet('showStartReviewModal', true)
            ->assertSet('grantToReviewId', $grant->id)
            ->call('startReview');

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaReviewingPlanning);
    });

    it('creates 4 assessment records when starting review', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview');

        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingPlanning)
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
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);

        startReviewForGrant($grant);

        $this->actingAs($polda);

        Livewire::test(Index::class)
            ->call('confirmStartReview', $grant->id)
            ->call('startReview')
            ->assertForbidden();
    });
});

describe('Grant Review — Per-Aspect Assessment', function () {
    it('allows Polda to approve an aspect', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

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
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

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
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

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

describe('Grant Review — Rejection Notification', function () {
    it('notifies Satker when Polda rejects grant', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $repository = app(\App\Repositories\GrantReviewRepository::class);
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

describe('Grant Review — Revision Resubmission', function () {
    it('allows Polda to re-review after Satker resubmits revision', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);

        // First review: Polda requests revision
        $repository = app(\App\Repositories\GrantReviewRepository::class);
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
            ->toBe(GrantStatus::PoldaRequestedPlanningRevision);

        // Satker resubmits
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::PoldaRequestedPlanningRevision->value,
            'status_sesudah' => GrantStatus::PlanningRevisionResubmitted->value,
            'keterangan' => 'Satker mengajukan revisi',
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
            ->toBe(GrantStatus::PoldaVerifiedPlanning);
    });

    it('notifies Satker when Polda requests revision', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $repository = app(\App\Repositories\GrantReviewRepository::class);
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

describe('Grant Review — Auto-Status Resolution', function () {
    it('auto-approves grant when all aspects fulfilled', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $repository = app(\App\Repositories\GrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $assessment) {
            $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaVerifiedPlanning);
    });

    it('auto-rejects grant when any aspect rejected', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $repository = app(\App\Repositories\GrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Rejected, 'Ditolak');
            } else {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaRejectedPlanning);
    });

    it('auto-requests revision when any aspect has revision and none rejected', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $repository = app(\App\Repositories\GrantReviewRepository::class);
        $assessments = $repository->getReviewAssessments($grant);

        foreach ($assessments as $index => $assessment) {
            if ($index === 0) {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Revision, 'Perlu revisi');
            } else {
                $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);
            }
        }

        $latestStatus = $grant->statusHistory()->latest('id')->first();
        expect($latestStatus->status_sesudah)->toBe(GrantStatus::PoldaRequestedPlanningRevision);
    });
});

describe('Grant Review — Validation', function () {
    it('requires result to be selected', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $this->actingAs($polda);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id))
            ->where('aspek', AssessmentAspect::Technical)
            ->first();

        Livewire::test(Review::class, ['grant' => $grant])
            ->call('openResultModal', $assessment->id, $assessment->aspek->label())
            ->call('submitResult')
            ->assertHasErrors(['result']);
    });

    it('requires remarks when result is not Fulfilled', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $this->actingAs($polda);

        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id))
            ->where('aspek', AssessmentAspect::Technical)
            ->first();

        Livewire::test(Review::class, ['grant' => $grant])
            ->call('openResultModal', $assessment->id, $assessment->aspek->label())
            ->set('result', AssessmentResult::Rejected->value)
            ->call('submitResult')
            ->assertHasErrors(['remarks']);
    });

    it('prevents re-submitting result for already evaluated aspect', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);
        startReviewForGrant($grant);

        $repository = app(\App\Repositories\GrantReviewRepository::class);
        $assessment = GrantAssessment::query()
            ->whereHas('statusHistory', fn ($q) => $q->where('id_hibah', $grant->id))
            ->where('aspek', AssessmentAspect::Technical)
            ->first();

        // Submit result via repository first
        $repository->submitAspectResult($assessment, $polda->unit, AssessmentResult::Fulfilled, null);

        $this->actingAs($polda);

        // Try to submit again via Livewire
        Livewire::test(Review::class, ['grant' => $grant])
            ->call('openResultModal', $assessment->id, $assessment->aspek->label())
            ->set('result', AssessmentResult::Rejected->value)
            ->set('remarks', 'Try to override')
            ->call('submitResult')
            ->assertStatus(422);
    });
});

describe('Grant Review — Access Control', function () {
    it('prevents accessing review page when grant is not under review', function () {
        $polda = createPoldaUser();
        $satker = createSatkerUserUnderPolda($polda);
        $grant = createSubmittedGrantForReview($satker);

        // Grant is submitted but NOT under review yet
        $this->actingAs($polda);

        $this->get(route('grant-review.review', $grant))
            ->assertForbidden();
    });

    it('prevents Polda from reviewing grants from non-child Satker', function () {
        $polda = createPoldaUser();
        $otherPolda = createPoldaUser();
        $unrelatedSatker = createSatkerUserUnderPolda($otherPolda);
        $grant = createSubmittedGrantForReview($unrelatedSatker);
        startReviewForGrant($grant);

        $this->actingAs($polda);

        $this->get(route('grant-review.review', $grant))
            ->assertForbidden();
    });
});
