<?php

// SPEC: Tag Management (Grant Categorization)
// This test file serves as executable specification for tag CRUD.
// See specs/features/grant-categorization.md for full feature spec.

use App\Livewire\TagManagement\Index;
use App\Models\OrgUnit;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

function createMabesUserForTags(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createSatuanKerjaUserForTags(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

    return $user;
}

describe('Tag Management — Happy Path', function () {
    it('allows Mabes to access the tag management page', function () {
        $mabes = createMabesUserForTags();

        $this->actingAs($mabes)
            ->get('/tag')
            ->assertSuccessful();
    });

    it('allows Mabes to create a tag', function () {
        $mabes = createMabesUserForTags();

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->set('name', 'Pendidikan')
            ->call('create')
            ->assertHasNoErrors();

        expect(Tag::where('name', 'Pendidikan')->exists())->toBeTrue();
    });

    it('resets the name input after creating a tag', function () {
        $mabes = createMabesUserForTags();

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->set('name', 'Kesehatan')
            ->call('create')
            ->assertSet('name', '');
    });

    it('displays created tags in the list', function () {
        $mabes = createMabesUserForTags();
        Tag::factory()->create(['name' => 'Infrastruktur']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->assertSeeText('Infrastruktur');
    });

    it('allows Mabes to update a tag', function () {
        $mabes = createMabesUserForTags();
        $tag = Tag::factory()->create(['name' => 'Lama']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('startEdit', $tag->id)
            ->assertSet('editingTagId', $tag->id)
            ->assertSet('editingName', 'Lama')
            ->assertSet('showEditModal', true)
            ->set('editingName', 'Baru')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        expect($tag->fresh()->name)->toBe('Baru');
    });
});

describe('Tag Management — Validation', function () {
    it('fails to create a tag with empty name', function () {
        $mabes = createMabesUserForTags();

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->set('name', '')
            ->call('create')
            ->assertHasErrors(['name' => 'required']);
    });

    it('fails to create a tag with duplicate name', function () {
        $mabes = createMabesUserForTags();
        Tag::factory()->create(['name' => 'Duplikat']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->set('name', 'Duplikat')
            ->call('create')
            ->assertHasErrors(['name' => 'unique']);
    });

    it('fails to update a tag with duplicate name', function () {
        $mabes = createMabesUserForTags();
        Tag::factory()->create(['name' => 'Existing']);
        $tag = Tag::factory()->create(['name' => 'Other']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('startEdit', $tag->id)
            ->set('editingName', 'Existing')
            ->call('update')
            ->assertHasErrors(['editingName' => 'unique']);
    });

    it('allows updating a tag to its own name', function () {
        $mabes = createMabesUserForTags();
        $tag = Tag::factory()->create(['name' => 'SameName']);

        $this->actingAs($mabes);

        Livewire::test(Index::class)
            ->call('startEdit', $tag->id)
            ->set('editingName', 'SameName')
            ->call('update')
            ->assertHasNoErrors();
    });
});

describe('Tag Management — Access Control', function () {
    it('redirects non-Mabes user from tag management to dashboard', function () {
        $satker = createSatuanKerjaUserForTags();

        $this->actingAs($satker)
            ->get('/tag')
            ->assertRedirect(route('dashboard'));
    });
});
