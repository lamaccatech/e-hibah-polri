<?php

use App\Enums\GrantStatus;
use App\Livewire\PoldaDashboard;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createDashboardPoldaUser(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createDashboardSatkerUser(User $poldaUser): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $poldaUser->id,
        ])
    );

    return $user;
}

function createPlanningGrantAtStatus(User $satkerUser, GrantStatus $targetStatus): Grant
{
    $grant = $satkerUser->unit->grants()->create(
        Grant::factory()->planned()->raw()
    );

    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::PlanningInitialized->value,
        'status_sesudah' => $targetStatus->value,
        'keterangan' => 'Status changed',
    ]);

    return $grant;
}

function createAgreementGrantAtStatus(User $satkerUser, GrantStatus $targetStatus): Grant
{
    $grant = $satkerUser->unit->grants()->create(
        Grant::factory()->directAgreement()->raw()
    );

    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::FillingReceptionData->value,
        'keterangan' => 'Initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::FillingReceptionData->value,
        'status_sesudah' => $targetStatus->value,
        'keterangan' => 'Status changed',
    ]);

    return $grant;
}

describe('Polda Dashboard — Access Control', function () {
    it('shows polda dashboard for Polda users', function () {
        $polda = createDashboardPoldaUser();

        $this->actingAs($polda)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(PoldaDashboard::class);
    });

    it('does not show polda dashboard for Satker users', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        $this->actingAs($satker)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSeeLivewire(PoldaDashboard::class);
    });

    it('does not show polda dashboard for Mabes users', function () {
        $mabes = User::factory()->create();
        $mabes->unit()->create(OrgUnit::factory()->mabes()->raw());

        $this->actingAs($mabes)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSeeLivewire(PoldaDashboard::class);
    });
});

describe('Polda Dashboard — Planning Stats', function () {
    it('counts planning grants created (submitted or beyond)', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createPlanningGrantAtStatus($satker, GrantStatus::PlanningSubmittedToPolda);
        createPlanningGrantAtStatus($satker, GrantStatus::PoldaReviewingPlanning);
        createPlanningGrantAtStatus($satker, GrantStatus::PoldaVerifiedPlanning);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertSee('3'); // planningCreated
    });

    it('does not count planning grants that have not been submitted', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        // Grant only at initialization — not submitted
        $grant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw()
        );
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
            'keterangan' => 'Initialized',
        ]);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 0);
    });

    it('counts planning unprocessed grants', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createPlanningGrantAtStatus($satker, GrantStatus::PlanningSubmittedToPolda);
        createPlanningGrantAtStatus($satker, GrantStatus::PlanningRevisionResubmitted);
        // This one is processing, should not be in unprocessed
        createPlanningGrantAtStatus($satker, GrantStatus::PoldaReviewingPlanning);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningUnprocessed'] === 2);
    });

    it('counts planning processing grants', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createPlanningGrantAtStatus($satker, GrantStatus::PoldaReviewingPlanning);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningProcessing'] === 1);
    });

    it('counts planning rejected grants', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createPlanningGrantAtStatus($satker, GrantStatus::PoldaRejectedPlanning);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningRejected'] === 1);
    });
});

describe('Polda Dashboard — Agreement Stats', function () {
    it('counts agreement grants created (submitted or beyond)', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createAgreementGrantAtStatus($satker, GrantStatus::AgreementSubmittedToPolda);
        createAgreementGrantAtStatus($satker, GrantStatus::PoldaReviewingAgreement);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementCreated'] === 2);
    });

    it('counts agreement unprocessed grants', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createAgreementGrantAtStatus($satker, GrantStatus::AgreementSubmittedToPolda);
        createAgreementGrantAtStatus($satker, GrantStatus::AgreementRevisionResubmitted);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementUnprocessed'] === 2);
    });

    it('counts agreement processing grants', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createAgreementGrantAtStatus($satker, GrantStatus::PoldaReviewingAgreement);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementProcessing'] === 1);
    });

    it('counts agreement rejected grants', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        createAgreementGrantAtStatus($satker, GrantStatus::PoldaRejectedAgreement);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementRejected'] === 1);
    });
});

describe('Polda Dashboard — Inbox', function () {
    it('shows unprocessed planning grants in inbox', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        $grant = createPlanningGrantAtStatus($satker, GrantStatus::PlanningSubmittedToPolda);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertSeeText($grant->nama_hibah)
            ->assertSeeText($satker->unit->nama_unit);
    });

    it('shows unprocessed agreement grants in inbox', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        $grant = createAgreementGrantAtStatus($satker, GrantStatus::AgreementSubmittedToPolda);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertSeeText($grant->nama_hibah);
    });

    it('does not show processing grants in inbox', function () {
        $polda = createDashboardPoldaUser();
        $satker = createDashboardSatkerUser($polda);

        $grant = createPlanningGrantAtStatus($satker, GrantStatus::PoldaReviewingPlanning);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('inbox', fn ($inbox) => $inbox->total() === 0);
    });

    it('shows empty state when no unprocessed grants', function () {
        $polda = createDashboardPoldaUser();

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertSeeText(__('page.dashboard.polda-inbox-empty'));
    });

    it('scopes inbox to child satkers only', function () {
        $polda = createDashboardPoldaUser();
        $otherPolda = createDashboardPoldaUser();
        $otherSatker = createDashboardSatkerUser($otherPolda);

        $grant = createPlanningGrantAtStatus($otherSatker, GrantStatus::PlanningSubmittedToPolda);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertDontSeeText($grant->nama_hibah)
            ->assertViewHas('inbox', fn ($inbox) => $inbox->total() === 0);
    });

    it('scopes stats to child satkers only', function () {
        $polda = createDashboardPoldaUser();
        $otherPolda = createDashboardPoldaUser();
        $otherSatker = createDashboardSatkerUser($otherPolda);

        createPlanningGrantAtStatus($otherSatker, GrantStatus::PlanningSubmittedToPolda);

        $this->actingAs($polda);

        Livewire::test(PoldaDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 0
                && $counts['planningUnprocessed'] === 0);
    });
});
