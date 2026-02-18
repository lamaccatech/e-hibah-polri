<?php

// SPEC: Donor Listing
// This test file serves as executable specification for the donor listing feature.
// See specs/features/donor-listing.md for full feature spec.

use App\Livewire\DonorListing\Index;
use App\Livewire\DonorListing\Show;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createMabesUserForDonorListing(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createSatuanKerjaUserForDonorListing(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

    return $user;
}

describe('Donor Listing — Happy Path', function () {
    it('allows Mabes to access the donor list page', function () {
        $mabes = createMabesUserForDonorListing();

        $this->actingAs($mabes)
            ->get(route('donor.index'))
            ->assertSuccessful();
    });

    it('displays donors with grant counts', function () {
        $mabes = createMabesUserForDonorListing();

        $satker = createSatuanKerjaUserForDonorListing();
        $donor = Donor::factory()->create(['nama' => 'PT Donor Test']);
        $satker->unit->grants()->create(
            Grant::factory()->planned()->raw(['id_pemberi_hibah' => $donor->id])
        );

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText('PT Donor Test')
            ->assertSeeText('1');
    });

    it('can search donors by name', function () {
        $mabes = createMabesUserForDonorListing();
        Donor::factory()->create(['nama' => 'PT Alpha']);
        Donor::factory()->create(['nama' => 'PT Beta']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText('PT Alpha')
            ->assertSeeText('PT Beta')
            ->set('search', 'Alpha')
            ->assertSeeText('PT Alpha')
            ->assertDontSeeText('PT Beta');
    });

    it('allows Mabes to view donor detail', function () {
        $mabes = createMabesUserForDonorListing();
        $donor = Donor::factory()->create(['nama' => 'PT Detail Donor']);

        $this->actingAs($mabes)
            ->get(route('donor.show', $donor))
            ->assertSuccessful()
            ->assertSeeText('PT Detail Donor');
    });

    it('displays linked grants on donor detail page', function () {
        $mabes = createMabesUserForDonorListing();
        $satker = createSatuanKerjaUserForDonorListing();
        $donor = Donor::factory()->create();
        $satker->unit->grants()->create(
            Grant::factory()->planned()->raw([
                'id_pemberi_hibah' => $donor->id,
                'nama_hibah' => 'Hibah Terkait',
            ])
        );

        $this->actingAs($mabes);

        Livewire::test(Show::class, ['donor' => $donor])
            ->assertSeeText('Hibah Terkait');
    });
});

describe('Donor Listing — Table Columns', function () {
    it('displays donor columns: asal, kategori, negara', function () {
        $mabes = createMabesUserForDonorListing();
        Donor::factory()->create([
            'nama' => 'PT Test Donor',
            'asal' => 'Jakarta',
            'kategori' => 'Swasta',
            'negara' => 'Indonesia',
        ]);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText('PT Test Donor')
            ->assertSeeText('Jakarta')
            ->assertSeeText('Swasta')
            ->assertSeeText('Indonesia');
    });
});

describe('Donor Listing — Search', function () {
    it('performs case-insensitive search', function () {
        $mabes = createMabesUserForDonorListing();
        Donor::factory()->create(['nama' => 'PT ALPHA OMEGA']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->set('search', 'alpha omega')
            ->assertSeeText('PT ALPHA OMEGA');
    });
});

describe('Donor Listing — Pagination', function () {
    it('paginates at 15 per page', function () {
        $mabes = createMabesUserForDonorListing();
        Donor::factory()->count(20)->create();

        $this->actingAs($mabes);

        $component = Livewire::test(Index::class);
        // First page should show 15 donors
        $donors = $component->viewData('donors');
        expect($donors->perPage())->toBe(15);
        expect($donors->count())->toBe(15);
    });
});

describe('Donor Listing — Detail Page', function () {
    it('displays all donor information fields', function () {
        $mabes = createMabesUserForDonorListing();
        $donor = Donor::factory()->create([
            'nama' => 'PT Full Info Donor',
            'asal' => 'Bandung',
            'negara' => 'Indonesia',
            'kategori' => 'BUMN',
        ]);

        $this->actingAs($mabes);

        Livewire::test(Show::class, ['donor' => $donor])
            ->assertSeeText('PT Full Info Donor')
            ->assertSeeText('Bandung')
            ->assertSeeText('Indonesia')
            ->assertSeeText('BUMN');
    });

    it('displays linked grant details on donor detail page', function () {
        $mabes = createMabesUserForDonorListing();
        $satker = createSatuanKerjaUserForDonorListing();
        $donor = Donor::factory()->create();
        $grant = $satker->unit->grants()->create(
            Grant::factory()->planned()->raw([
                'id_pemberi_hibah' => $donor->id,
                'nama_hibah' => 'Hibah Detail Test',
            ])
        );

        $this->actingAs($mabes);

        Livewire::test(Show::class, ['donor' => $donor])
            ->assertSeeText('Hibah Detail Test')
            ->assertSeeText($satker->unit->nama_unit);
    });
});

describe('Donor Listing — Access Control', function () {
    it('redirects non-Mabes user from donor list to dashboard', function () {
        $satker = createSatuanKerjaUserForDonorListing();

        $this->actingAs($satker)
            ->get(route('donor.index'))
            ->assertRedirect(route('dashboard'));
    });

    it('redirects non-Mabes user from donor detail to dashboard', function () {
        $satker = createSatuanKerjaUserForDonorListing();
        $donor = Donor::factory()->create();

        $this->actingAs($satker)
            ->get(route('donor.show', $donor))
            ->assertRedirect(route('dashboard'));
    });
});
