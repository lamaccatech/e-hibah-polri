<?php

use App\Enums\GrantStatus;
use App\Livewire\MabesDashboard;
use App\Models\Grant;
use App\Models\GrantBudgetPlan;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createMabesUser(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createMabesPoldaUser(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createMabesSatkerUser(User $poldaUser): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $poldaUser->id,
        ])
    );

    return $user;
}

function createMabesPlanningGrantAtStatus(User $satkerUser, GrantStatus $targetStatus): Grant
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

function createMabesAgreementGrantAtStatus(User $satkerUser, GrantStatus $targetStatus): Grant
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

describe('Mabes Dashboard — Access Control', function () {
    it('shows mabes dashboard for Mabes users', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(MabesDashboard::class);
    });

    it('does not show mabes dashboard for Satker users', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);

        $this->actingAs($satker)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSeeLivewire(MabesDashboard::class);
    });

    it('does not show mabes dashboard for Polda users', function () {
        $polda = createMabesPoldaUser();

        $this->actingAs($polda)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSeeLivewire(MabesDashboard::class);
    });
});

describe('Mabes Dashboard — Planning Stats', function () {
    it('counts planning grants that reached Polda verification or beyond', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesPlanningGrantAtStatus($satker, GrantStatus::PoldaVerifiedPlanning);
        createMabesPlanningGrantAtStatus($satker, GrantStatus::MabesReviewingPlanning);
        createMabesPlanningGrantAtStatus($satker, GrantStatus::MabesVerifiedPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 3);
    });

    it('does not count planning grants only at Polda review level', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        // Only submitted to Polda — not yet verified by Polda
        createMabesPlanningGrantAtStatus($satker, GrantStatus::PlanningSubmittedToPolda);
        createMabesPlanningGrantAtStatus($satker, GrantStatus::PoldaReviewingPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 0);
    });

    it('counts planning unprocessed grants (latest status = PoldaVerifiedPlanning)', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesPlanningGrantAtStatus($satker, GrantStatus::PoldaVerifiedPlanning);
        // This one is being reviewed by Mabes, not unprocessed
        createMabesPlanningGrantAtStatus($satker, GrantStatus::MabesReviewingPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningUnprocessed'] === 1);
    });

    it('counts planning processing grants', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesPlanningGrantAtStatus($satker, GrantStatus::MabesReviewingPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningProcessing'] === 1);
    });

    it('counts planning rejected grants', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesPlanningGrantAtStatus($satker, GrantStatus::MabesRejectedPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningRejected'] === 1);
    });
});

describe('Mabes Dashboard — Agreement Stats', function () {
    it('counts agreement grants that reached Polda verification or beyond', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesAgreementGrantAtStatus($satker, GrantStatus::PoldaVerifiedAgreement);
        createMabesAgreementGrantAtStatus($satker, GrantStatus::MabesReviewingAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementCreated'] === 2);
    });

    it('counts agreement unprocessed grants (latest status = PoldaVerifiedAgreement)', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesAgreementGrantAtStatus($satker, GrantStatus::PoldaVerifiedAgreement);
        createMabesAgreementGrantAtStatus($satker, GrantStatus::MabesReviewingAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementUnprocessed'] === 1);
    });

    it('counts agreement processing grants', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesAgreementGrantAtStatus($satker, GrantStatus::MabesReviewingAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementProcessing'] === 1);
    });

    it('counts agreement rejected grants', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        createMabesAgreementGrantAtStatus($satker, GrantStatus::MabesRejectedAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementRejected'] === 1);
    });
});

describe('Mabes Dashboard — System-wide Scope', function () {
    it('counts grants from all satkers across different poldas', function () {
        $polda1 = createMabesPoldaUser();
        $satker1 = createMabesSatkerUser($polda1);
        $polda2 = createMabesPoldaUser();
        $satker2 = createMabesSatkerUser($polda2);
        $mabes = createMabesUser();

        createMabesPlanningGrantAtStatus($satker1, GrantStatus::PoldaVerifiedPlanning);
        createMabesPlanningGrantAtStatus($satker2, GrantStatus::PoldaVerifiedPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 2
                && $counts['planningUnprocessed'] === 2);
    });
});

describe('Mabes Dashboard — Realization', function () {
    it('returns realization data with correct plan and realization sums', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        // Grant with budget plans — submitted to polda (plan scope)
        $planGrant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw(['bentuk_hibah' => 'UANG'])
        );
        $planGrant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => 'Submitted',
        ]);
        $planGrant->budgetPlans()->create(
            GrantBudgetPlan::factory()->raw(['nilai' => 1000000])
        );

        // Grant with budget plans — reached signed agreement (realization scope)
        $realGrant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw(['bentuk_hibah' => 'UANG'])
        );
        $realGrant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => 'Submitted',
        ]);
        $realGrant->statusHistory()->create([
            'status_sesudah' => GrantStatus::UploadingSignedAgreement->value,
            'keterangan' => 'Signed',
        ]);
        $realGrant->budgetPlans()->create(
            GrantBudgetPlan::factory()->raw(['nilai' => 500000])
        );

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('realization', function (array $realization) {
                return $realization['money']['plan'] === 1500000.0
                    && $realization['money']['realization'] === 500000.0;
            });
    });

    it('filters realization by grant form type', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        // Goods grant
        $goodsGrant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw(['bentuk_hibah' => 'BARANG'])
        );
        $goodsGrant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => 'Submitted',
        ]);
        $goodsGrant->budgetPlans()->create(
            GrantBudgetPlan::factory()->raw(['nilai' => 2000000])
        );

        // Money grant
        $moneyGrant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw(['bentuk_hibah' => 'UANG'])
        );
        $moneyGrant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => 'Submitted',
        ]);
        $moneyGrant->budgetPlans()->create(
            GrantBudgetPlan::factory()->raw(['nilai' => 3000000])
        );

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('realization', function (array $realization) {
                return $realization['goodsServices']['plan'] === 2000000.0
                    && $realization['money']['plan'] === 3000000.0;
            });
    });
});

describe('Mabes Dashboard — Yearly Trend', function () {
    it('returns yearly trend data grouped by year', function () {
        $polda = createMabesPoldaUser();
        $satker = createMabesSatkerUser($polda);
        $mabes = createMabesUser();

        $grant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw()
        );
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => 'Submitted',
        ]);
        $grant->budgetPlans()->create(
            GrantBudgetPlan::factory()->raw(['nilai' => 1000000])
        );

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('yearlyTrend', function (array $trend) {
                return count($trend) === 1
                    && $trend[0]['year'] === (string) now()->year
                    && $trend[0]['plan'] === 1000000.0;
            });
    });

    it('returns empty trend when no grants exist', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('yearlyTrend', fn (array $trend) => count($trend) === 0);
    });
});
