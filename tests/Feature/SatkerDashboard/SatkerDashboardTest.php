<?php

// SPEC: Dashboard — Satker
// See specs/features/dashboard.md for full feature spec.

use App\Models\OrgUnit;
use App\Models\User;

function createSatkerUserForDashboardTest(): User
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

describe('Satker Dashboard', function () {
    it('shows grant type selection with HL and HDR cards', function () {
        $satker = createSatkerUserForDashboardTest();

        $this->actingAs($satker)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSeeText(__('page.dashboard.direct-grant-title'))
            ->assertSeeText(__('page.dashboard.planned-grant-title'));
    });

    it('shows HDR card as disabled with "Segera Hadir" badge', function () {
        $satker = createSatkerUserForDashboardTest();

        $this->actingAs($satker)
            ->get('/dashboard')
            ->assertSeeText(__('page.dashboard.planned-grant-badge'));
    });

    it('renders direct grant sub-options section', function () {
        $satker = createSatkerUserForDashboardTest();

        $this->actingAs($satker)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSeeText(__('page.dashboard.proposal-title'))
            ->assertSeeText(__('page.dashboard.agreement-title'));
    });

    it('contains link to grant planning create page', function () {
        $satker = createSatkerUserForDashboardTest();

        $this->actingAs($satker)
            ->get('/dashboard')
            ->assertSee(route('grant-planning.create'));
    });

    it('contains link to grant agreement create page', function () {
        $satker = createSatkerUserForDashboardTest();

        $this->actingAs($satker)
            ->get('/dashboard')
            ->assertSee(route('grant-agreement.create'));
    });

    it('does not show Polda or Mabes dashboard for Satker users', function () {
        $satker = createSatkerUserForDashboardTest();

        $this->actingAs($satker)
            ->get('/dashboard')
            ->assertDontSee('livewire/polda-dashboard')
            ->assertDontSee('livewire/mabes-dashboard');
    });
});
