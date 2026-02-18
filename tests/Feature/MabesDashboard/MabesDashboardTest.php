<?php

use App\Enums\GrantStatus;
use App\Livewire\MabesDashboard;
use App\Models\Grant;
use App\Models\GrantBudgetPlan;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createMabesUserForDashboard(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createPoldaUserForDashboard(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createSatkerUserForDashboard(User $poldaUser): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $poldaUser->id,
        ])
    );

    return $user;
}

function createPlanningGrantForDashboard(User $satkerUser, GrantStatus $targetStatus): Grant
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

function createAgreementGrantForDashboard(User $satkerUser, GrantStatus $targetStatus): Grant
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
        $mabes = createMabesUserForDashboard();

        $this->actingAs($mabes)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(MabesDashboard::class);
    });

    it('does not show mabes dashboard for Satker users', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);

        $this->actingAs($satker)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSeeLivewire(MabesDashboard::class);
    });

    it('does not show mabes dashboard for Polda users', function () {
        $polda = createPoldaUserForDashboard();

        $this->actingAs($polda)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSeeLivewire(MabesDashboard::class);
    });
});

describe('Mabes Dashboard — Planning Stats', function () {
    it('counts planning grants that reached Polda verification or beyond', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createPlanningGrantForDashboard($satker, GrantStatus::PoldaVerifiedPlanning);
        createPlanningGrantForDashboard($satker, GrantStatus::MabesReviewingPlanning);
        createPlanningGrantForDashboard($satker, GrantStatus::MabesVerifiedPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 3);
    });

    it('does not count planning grants only at Polda review level', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        // Only submitted to Polda — not yet verified by Polda
        createPlanningGrantForDashboard($satker, GrantStatus::PlanningSubmittedToPolda);
        createPlanningGrantForDashboard($satker, GrantStatus::PoldaReviewingPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 0);
    });

    it('counts planning unprocessed grants (latest status = PoldaVerifiedPlanning)', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createPlanningGrantForDashboard($satker, GrantStatus::PoldaVerifiedPlanning);
        // This one is being reviewed by Mabes, not unprocessed
        createPlanningGrantForDashboard($satker, GrantStatus::MabesReviewingPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningUnprocessed'] === 1);
    });

    it('counts planning processing grants', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createPlanningGrantForDashboard($satker, GrantStatus::MabesReviewingPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningProcessing'] === 1);
    });

    it('counts planning rejected grants', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createPlanningGrantForDashboard($satker, GrantStatus::MabesRejectedPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningRejected'] === 1);
    });
});

describe('Mabes Dashboard — Agreement Stats', function () {
    it('counts agreement grants that reached Polda verification or beyond', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createAgreementGrantForDashboard($satker, GrantStatus::PoldaVerifiedAgreement);
        createAgreementGrantForDashboard($satker, GrantStatus::MabesReviewingAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementCreated'] === 2);
    });

    it('counts agreement unprocessed grants (latest status = PoldaVerifiedAgreement)', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createAgreementGrantForDashboard($satker, GrantStatus::PoldaVerifiedAgreement);
        createAgreementGrantForDashboard($satker, GrantStatus::MabesReviewingAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementUnprocessed'] === 1);
    });

    it('counts agreement processing grants', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createAgreementGrantForDashboard($satker, GrantStatus::MabesReviewingAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementProcessing'] === 1);
    });

    it('counts agreement rejected grants', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        createAgreementGrantForDashboard($satker, GrantStatus::MabesRejectedAgreement);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['agreementRejected'] === 1);
    });
});

describe('Mabes Dashboard — System-wide Scope', function () {
    it('counts grants from all satkers across different poldas', function () {
        $polda1 = createPoldaUserForDashboard();
        $satker1 = createSatkerUserForDashboard($polda1);
        $polda2 = createPoldaUserForDashboard();
        $satker2 = createSatkerUserForDashboard($polda2);
        $mabes = createMabesUserForDashboard();

        createPlanningGrantForDashboard($satker1, GrantStatus::PoldaVerifiedPlanning);
        createPlanningGrantForDashboard($satker2, GrantStatus::PoldaVerifiedPlanning);

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('counts', fn (array $counts) => $counts['planningCreated'] === 2
                && $counts['planningUnprocessed'] === 2);
    });
});

describe('Mabes Dashboard — Realization', function () {
    it('returns realization data with correct plan and realization sums', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

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
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

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
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

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
        $mabes = createMabesUserForDashboard();

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('yearlyTrend', fn (array $trend) => count($trend) === 0);
    });

    it('groups trend data by multiple years', function () {
        $polda = createPoldaUserForDashboard();
        $satker = createSatkerUserForDashboard($polda);
        $mabes = createMabesUserForDashboard();

        // Grant from current year
        $currentYearGrant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw()
        );
        $currentYearGrant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => 'Submitted',
        ]);
        $currentYearGrant->budgetPlans()->create(
            GrantBudgetPlan::factory()->raw(['nilai' => 1000000])
        );

        // Grant from previous year — set created_at via query builder
        $lastYearGrant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw()
        );
        Grant::withoutTimestamps(fn () => $lastYearGrant->forceFill(['created_at' => now()->subYear()])->save());
        $lastYearGrant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => 'Submitted',
        ]);
        $lastYearGrant->budgetPlans()->create(
            GrantBudgetPlan::factory()->raw(['nilai' => 2000000])
        );

        $this->actingAs($mabes);

        Livewire::test(MabesDashboard::class)
            ->assertViewHas('yearlyTrend', function (array $trend) {
                return count($trend) >= 2;
            });
    });
});
