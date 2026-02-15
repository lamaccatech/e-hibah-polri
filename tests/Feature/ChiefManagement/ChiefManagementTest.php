<?php

// SPEC: Unit Chief Management
// This test file serves as executable specification for unit chief CRUD.
// See specs/features/unit-chief-management.md for full feature spec.

use App\Livewire\ChiefManagement\Create;
use App\Livewire\ChiefManagement\Edit;
use App\Livewire\ChiefManagement\Index;
use App\Models\OrgUnit;
use App\Models\OrgUnitChief;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function createSatkerUser(?int $parentUserId = null): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $parentUserId,
        ])
    );

    return $user;
}

function createMabesUserForChiefTest(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

describe('Chief Management — Happy Path — List', function () {
    it('allows Satker to access the chief list page', function () {
        $satker = createSatkerUser();

        $this->actingAs($satker)
            ->get('/kepala-satker')
            ->assertSuccessful();
    });

    it('displays chiefs belonging to the user unit', function () {
        $satker = createSatkerUser();

        $satker->unit->chiefs()->save(OrgUnitChief::factory()->make());
        $satker->unit->chiefs()->save(OrgUnitChief::factory()->active()->make());

        $this->actingAs($satker);

        Livewire::test(Index::class)
            ->assertSee($satker->unit->chiefs->first()->nama_lengkap)
            ->assertSee($satker->unit->chiefs->last()->nama_lengkap);
    });
});

describe('Chief Management — Happy Path — Create', function () {
    it('allows Satker to access the create chief form', function () {
        $satker = createSatkerUser();

        $this->actingAs($satker)
            ->get('/kepala-satker/create')
            ->assertSuccessful();
    });

    it('allows Satker to create a chief with signature', function () {
        Storage::fake();
        $satker = createSatkerUser();

        $this->actingAs($satker);

        $file = UploadedFile::fake()->image('signature.png');

        Livewire::test(Create::class)
            ->set('fullName', 'Budi Santoso')
            ->set('position', 'Kepala Unit')
            ->set('rank', 'Komisaris')
            ->set('nrp', '12345678')
            ->set('signature', $file)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('chief.index'));

        $chief = OrgUnitChief::where('nama_lengkap', 'Budi Santoso')->first();
        expect($chief)->not->toBeNull();
        expect($chief->jabatan)->toBe('Kepala Unit');
        expect($chief->pangkat)->toBe('Komisaris');
        expect($chief->nrp)->toBe('12345678');
        expect($chief->tanda_tangan)->not->toBeNull();
        expect($chief->id_unit)->toBe($satker->id);
    });
});

describe('Chief Management — Happy Path — Assign', function () {
    it('allows Satker to designate a chief as active', function () {
        $satker = createSatkerUser();
        $chief1 = $satker->unit->chiefs()->save(OrgUnitChief::factory()->active()->make());
        $chief2 = $satker->unit->chiefs()->save(OrgUnitChief::factory()->make());

        $this->actingAs($satker);

        Livewire::test(Index::class)
            ->call('assign', $chief2->id)
            ->assertHasNoErrors();

        expect($chief1->fresh()->sedang_menjabat)->toBeFalse();
        expect($chief2->fresh()->sedang_menjabat)->toBeTrue();
    });
});

describe('Chief Management — Happy Path — Update', function () {
    it('allows Satker to access the edit chief form', function () {
        $satker = createSatkerUser();
        $chief = $satker->unit->chiefs()->save(OrgUnitChief::factory()->make());

        $this->actingAs($satker)
            ->get('/kepala-satker/'.$chief->id.'/edit')
            ->assertSuccessful();
    });

    it('allows Satker to update chief data', function () {
        $satker = createSatkerUser();
        $chief = $satker->unit->chiefs()->save(OrgUnitChief::factory()->make());

        $this->actingAs($satker);

        Livewire::test(Edit::class, ['chief' => $chief])
            ->set('fullName', 'Nama Diperbarui')
            ->set('position', 'Jabatan Baru')
            ->set('rank', 'Pangkat Baru')
            ->set('nrp', '99999999')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('chief.index'));

        $chief->refresh();
        expect($chief->nama_lengkap)->toBe('Nama Diperbarui');
        expect($chief->jabatan)->toBe('Jabatan Baru');
        expect($chief->pangkat)->toBe('Pangkat Baru');
        expect($chief->nrp)->toBe('99999999');
    });

    it('allows Satker to update chief signature', function () {
        Storage::fake();
        $satker = createSatkerUser();
        $chief = $satker->unit->chiefs()->save(OrgUnitChief::factory()->make([
            'tanda_tangan' => 'signatures/old.png',
        ]));

        $this->actingAs($satker);

        $file = UploadedFile::fake()->image('new-signature.png');

        Livewire::test(Edit::class, ['chief' => $chief])
            ->set('signature', $file)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('chief.index'));

        $chief->refresh();
        expect($chief->tanda_tangan)->not->toBe('signatures/old.png');
    });
});

describe('Chief Management — Access Control', function () {
    it('redirects non-Satker user from chief list to dashboard', function () {
        $mabes = createMabesUserForChiefTest();

        $this->actingAs($mabes)
            ->get('/kepala-satker')
            ->assertRedirect(route('dashboard'));
    });

    it('redirects non-Satker user from create form to dashboard', function () {
        $mabes = createMabesUserForChiefTest();

        $this->actingAs($mabes)
            ->get('/kepala-satker/create')
            ->assertRedirect(route('dashboard'));
    });

    it('redirects non-Satker user from edit form to dashboard', function () {
        $satker = createSatkerUser();
        $chief = $satker->unit->chiefs()->save(OrgUnitChief::factory()->make());
        $mabes = createMabesUserForChiefTest();

        $this->actingAs($mabes)
            ->get('/kepala-satker/'.$chief->id.'/edit')
            ->assertRedirect(route('dashboard'));
    });
});
