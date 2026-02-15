<?php

use App\Enums\GrantStatus;
use App\Livewire\GrantReview\Index;
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
