<?php

use App\Enums\FileType;
use App\Enums\GrantStatus;
use App\Models\File;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function createSatkerUserForDownload(): User
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

function createGrantWithFile(User $satkerUser, FileType $fileType = FileType::DonorLetter): array
{
    $grant = $satkerUser->unit->grants()->create(
        Grant::factory()->planned()->raw()
    );

    $statusHistory = $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Initialized',
    ]);

    Storage::fake();
    $file = $statusHistory->files()->create([
        'file_type' => $fileType,
        'name' => 'test-document.pdf',
        'path' => 'test/test-document.pdf',
        'mime_type' => 'application/pdf',
        'size_in_bytes' => 1024,
    ]);
    Storage::put('test/test-document.pdf', 'fake content');

    return [$grant, $file];
}

it('allows satker user to download their grant status history file', function () {
    $user = createSatkerUserForDownload();
    [$grant, $file] = createGrantWithFile($user);

    $this->actingAs($user)
        ->get(route('grant-file.download', [$grant, $file]))
        ->assertSuccessful();
});

it('returns 403 when user cannot view the grant', function () {
    $owner = createSatkerUserForDownload();
    [$grant, $file] = createGrantWithFile($owner);

    $otherPolda = User::factory()->create();
    $otherPolda->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    $otherUser = User::factory()->create();
    $otherUser->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
        'id_unit_atasan' => $otherPolda->id,
    ]));

    $this->actingAs($otherUser)
        ->get(route('grant-file.download', [$grant, $file]))
        ->assertForbidden();
});

it('returns 404 when file does not belong to grant status history', function () {
    $user = createSatkerUserForDownload();
    $grant = $user->unit->grants()->create(
        Grant::factory()->planned()->raw()
    );

    // Create file on a different grant's status history
    $otherGrant = $user->unit->grants()->create(
        Grant::factory()->planned()->raw()
    );
    $otherHistory = $otherGrant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Other grant',
    ]);

    Storage::fake();
    $file = $otherHistory->files()->create([
        'file_type' => FileType::DonorLetter,
        'name' => 'other-document.pdf',
        'path' => 'test/other-document.pdf',
        'mime_type' => 'application/pdf',
        'size_in_bytes' => 1024,
    ]);

    $this->actingAs($user)
        ->get(route('grant-file.download', [$grant, $file]))
        ->assertNotFound();
});

it('excludes generated documents from uploaded files list', function () {
    $user = createSatkerUserForDownload();
    $grant = $user->unit->grants()->create(
        Grant::factory()->planned()->raw()
    );

    $statusHistory = $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Initialized',
    ]);

    $statusHistory->files()->create([
        'file_type' => FileType::GeneratedDocument,
        'name' => 'generated.pdf',
        'path' => 'test/generated.pdf',
        'mime_type' => 'application/pdf',
        'size_in_bytes' => 1024,
    ]);

    $statusHistory->files()->create([
        'file_type' => FileType::DonorLetter,
        'name' => 'donor-letter.pdf',
        'path' => 'test/donor-letter.pdf',
        'mime_type' => 'application/pdf',
        'size_in_bytes' => 1024,
    ]);

    $repo = app(\App\Repositories\GrantDetailRepository::class);
    $files = $repo->getUploadedFiles($grant);

    expect($files)->toHaveCount(1)
        ->and($files->first()->file_type)->toBe(FileType::DonorLetter);
});

it('requires authentication to download', function () {
    $user = createSatkerUserForDownload();
    [$grant, $file] = createGrantWithFile($user);

    $this->get(route('grant-file.download', [$grant, $file]))
        ->assertRedirect('/login');
});
