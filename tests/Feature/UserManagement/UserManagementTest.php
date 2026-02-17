<?php

// SPEC: User Management
// This test file serves as executable specification for user + org unit CRUD.
// See specs/features/user-management.md for full feature spec.

use App\Enums\UnitLevel;
use App\Livewire\UserManagement\Create;
use App\Livewire\UserManagement\Edit;
use App\Livewire\UserManagement\Index;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createMabesUser(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createSatuanKerjaUser(?int $parentUserId = null): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $parentUserId,
        ])
    );

    return $user;
}

describe('User Management — Search', function () {
    it('filters users by unit name', function () {
        $mabes = createMabesUser();
        $matchUser = createSatuanKerjaUser($mabes->id);
        $matchUser->unit->update(['nama_unit' => 'Polres Jakarta Selatan']);

        $otherUser = createSatuanKerjaUser($mabes->id);
        $otherUser->unit->update(['nama_unit' => 'Polres Bandung']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->set('search', 'Jakarta')
            ->assertSeeText('Polres Jakarta Selatan')
            ->assertDontSeeText('Polres Bandung');
    });

    it('shows all users when search is empty', function () {
        $mabes = createMabesUser();
        $user1 = createSatuanKerjaUser($mabes->id);
        $user2 = createSatuanKerjaUser($mabes->id);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText($user1->unit->nama_unit)
            ->assertSeeText($user2->unit->nama_unit);
    });

    it('shows empty state when search has no results', function () {
        $mabes = createMabesUser();
        createSatuanKerjaUser($mabes->id);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->set('search', 'nonexistent unit xyz')
            ->assertSeeText(__('page.user-management.empty-state'));
    });
});

describe('User Management — Happy Path — Create', function () {
    it('allows Mabes to access the user list page', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes)
            ->get('/user')
            ->assertSuccessful();
    });

    it('allows Mabes to access the create user form', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes)
            ->get('/user/create')
            ->assertSuccessful();
    });

    it('allows Mabes to create a user with an org unit', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes);

        Livewire::test(Create::class)
            ->set('email', 'newuser@example.com')
            ->set('password', 'password')
            ->set('passwordConfirmation', 'password')
            ->set('unitName', 'Unit Baru')
            ->set('code', 'UB01')
            ->set('unitLevel', UnitLevel::SatuanKerja->value)
            ->set('parentUnitId', $mabes->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('user.index'));

        $newUser = User::where('email', 'newuser@example.com')->first();
        expect($newUser)->not->toBeNull();
        expect($newUser->unit)->not->toBeNull();
        expect($newUser->unit->nama_unit)->toBe('Unit Baru');
        expect($newUser->unit->kode)->toBe('UB01');
        expect($newUser->unit->level_unit)->toBe(UnitLevel::SatuanKerja);
        expect($newUser->unit->id_unit_atasan)->toBe($mabes->id);
    });
});

describe('User Management — Happy Path — Update', function () {
    it('allows Mabes to access the edit user form', function () {
        $mabes = createMabesUser();
        $target = createSatuanKerjaUser($mabes->id);

        $this->actingAs($mabes)
            ->get('/user/'.$target->id.'/edit')
            ->assertSuccessful();
    });

    it('allows Mabes to update user email and unit data', function () {
        $mabes = createMabesUser();
        $target = createSatuanKerjaUser($mabes->id);

        $this->actingAs($mabes);

        Livewire::test(Edit::class, ['user' => $target])
            ->set('email', 'updated@example.com')
            ->set('unitName', 'Unit Diperbarui')
            ->set('code', 'UD01')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('user.index'));

        $target->refresh();
        $target->load('unit');
        expect($target->email)->toBe('updated@example.com');
        expect($target->unit->nama_unit)->toBe('Unit Diperbarui');
        expect($target->unit->kode)->toBe('UD01');
    });
});

describe('User Management — Happy Path — Delete', function () {
    it('allows Mabes to soft-delete a user and their unit', function () {
        $mabes = createMabesUser();
        $target = createSatuanKerjaUser($mabes->id);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmDelete', $target->id)
            ->call('delete')
            ->assertHasNoErrors();

        expect(User::find($target->id))->toBeNull();
        expect(User::withTrashed()->find($target->id)->deleted_at)->not->toBeNull();
        expect(OrgUnit::withTrashed()->where('id_user', $target->id)->first()->deleted_at)->not->toBeNull();
    });

    it('prevents deleted user from logging in', function () {
        $mabes = createMabesUser();
        $target = createSatuanKerjaUser($mabes->id);
        $targetEmail = $target->email;

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmDelete', $target->id)
            ->call('delete');

        $this->post('/logout');

        $this->post('/login', [
            'email' => $targetEmail,
            'password' => 'password',
        ]);

        $this->assertGuest();
    });
});

describe('User Management — Validation — Create', function () {
    it('fails with duplicate email', function () {
        $mabes = createMabesUser();
        $existing = createSatuanKerjaUser($mabes->id);

        $this->actingAs($mabes);

        Livewire::test(Create::class)
            ->set('email', $existing->email)
            ->set('password', 'password')
            ->set('passwordConfirmation', 'password')
            ->set('unitName', 'Unit Test')
            ->set('code', 'UT01')
            ->set('unitLevel', UnitLevel::SatuanKerja->value)
            ->set('parentUnitId', $mabes->id)
            ->call('save')
            ->assertHasErrors(['email']);
    });

    it('fails with missing required fields', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes);

        Livewire::test(Create::class)
            ->call('save')
            ->assertHasErrors(['email', 'password', 'unitName', 'code', 'unitLevel', 'parentUnitId']);
    });

    it('fails with mismatched password confirmation', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes);

        Livewire::test(Create::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('passwordConfirmation', 'different')
            ->set('unitName', 'Unit Test')
            ->set('code', 'UT01')
            ->set('unitLevel', UnitLevel::SatuanKerja->value)
            ->set('parentUnitId', $mabes->id)
            ->call('save')
            ->assertHasErrors(['password']);
    });

    it('fails with invalid parent unit reference', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes);

        Livewire::test(Create::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('passwordConfirmation', 'password')
            ->set('unitName', 'Unit Test')
            ->set('code', 'UT01')
            ->set('unitLevel', UnitLevel::SatuanKerja->value)
            ->set('parentUnitId', '99999')
            ->call('save')
            ->assertHasErrors(['parentUnitId']);
    });

    it('fails with invalid level_unit value', function () {
        $mabes = createMabesUser();

        $this->actingAs($mabes);

        Livewire::test(Create::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('passwordConfirmation', 'password')
            ->set('unitName', 'Unit Test')
            ->set('code', 'UT01')
            ->set('unitLevel', 'invalid_level')
            ->set('parentUnitId', $mabes->id)
            ->call('save')
            ->assertHasErrors(['unitLevel']);
    });
});

describe('User Management — Validation — Delete', function () {
    it('rejects deletion when unit has active grants', function () {
        $mabes = createMabesUser();
        $target = createSatuanKerjaUser($mabes->id);

        $target->unit->grants()->save(Grant::factory()->make());

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmDelete', $target->id)
            ->call('delete')
            ->assertHasErrors(['delete']);

        expect(User::find($target->id))->not->toBeNull();
    });

    it('rejects deletion when unit has child units', function () {
        $mabes = createMabesUser();
        $parent = createSatuanKerjaUser($mabes->id);
        createSatuanKerjaUser($parent->id);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('confirmDelete', $parent->id)
            ->call('delete')
            ->assertHasErrors(['delete']);

        expect(User::find($parent->id))->not->toBeNull();
    });
});

describe('User Management — Access Control', function () {
    it('redirects non-Mabes user from user list to dashboard', function () {
        $satuanKerja = createSatuanKerjaUser();

        $this->actingAs($satuanKerja)
            ->get('/user')
            ->assertRedirect(route('dashboard'));
    });

    it('redirects non-Mabes user from create form to dashboard', function () {
        $satuanKerja = createSatuanKerjaUser();

        $this->actingAs($satuanKerja)
            ->get('/user/create')
            ->assertRedirect(route('dashboard'));
    });

    it('redirects non-Mabes user from edit form to dashboard', function () {
        $mabes = createMabesUser();
        $satuanKerja = createSatuanKerjaUser($mabes->id);
        $target = createSatuanKerjaUser($mabes->id);

        $this->actingAs($satuanKerja)
            ->get('/user/'.$target->id.'/edit')
            ->assertRedirect(route('dashboard'));
    });
});
