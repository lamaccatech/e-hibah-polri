<?php

use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\GrantType;
use App\Livewire\GrantDetail\Show;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function createSatkerUserForRevise(): User
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

function createGrantWithAgreementNumber(User $user, ?Carbon $issuedAt = null): Grant
{
    $donor = Donor::factory()->create();
    $grant = $user->unit->grants()->create(
        Grant::factory()->raw([
            'jenis_hibah' => GrantType::Direct->value,
            'tahapan' => GrantStage::Agreement->value,
            'ada_usulan' => false,
            'id_pemberi_hibah' => $donor->id,
        ])
    );

    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::FillingReceptionData->value,
        'keterangan' => 'Agreement initialized',
    ]);

    $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::FillingReceptionData->value,
        'status_sesudah' => GrantStatus::AgreementNumberIssued->value,
        'keterangan' => 'Agreement number issued',
        'created_at' => $issuedAt ?? now(),
    ]);

    // Create the agreement numbering record
    $month = ($issuedAt ?? now())->month;
    $year = ($issuedAt ?? now())->year;
    $romanMonths = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];

    $grant->numberings()->create([
        'nomor' => "NPH/{$year}/{$romanMonths[$month]}/1/{$user->unit->kode}",
        'kode' => 'NPH',
        'nomor_urut' => 1,
        'bulan' => $month,
        'tahun' => $year,
        'tahapan' => GrantStage::Agreement->value,
        'kode_satuan_kerja' => $user->unit->kode,
    ]);

    return $grant;
}

// -------------------------------------------------------------------
// Revise Agreement Number Month
// -------------------------------------------------------------------

describe('Grant Detail — Revise Agreement Number Month', function () {
    it('shows revise button when current month is later than issuance month', function () {
        // Issued in January 2026
        $this->travelTo(Carbon::create(2026, 1, 15));
        $user = createSatkerUserForRevise();
        $grant = createGrantWithAgreementNumber($user, Carbon::create(2026, 1, 15));

        // Now it's February 2026
        $this->travelTo(Carbon::create(2026, 2, 10));

        Livewire::actingAs($user)
            ->test(Show::class, ['grant' => $grant])
            ->assertSeeText(__('page.grant-detail.revise-number-button'));
    });

    it('hides revise button when current month is the same as issuance month', function () {
        $this->travelTo(Carbon::create(2026, 3, 15));
        $user = createSatkerUserForRevise();
        $grant = createGrantWithAgreementNumber($user, Carbon::create(2026, 3, 10));

        Livewire::actingAs($user)
            ->test(Show::class, ['grant' => $grant])
            ->assertDontSeeText(__('page.grant-detail.revise-number-button'));
    });

    it('hides revise button when year has changed since issuance', function () {
        // Issued in December 2025
        $this->travelTo(Carbon::create(2025, 12, 15));
        $user = createSatkerUserForRevise();
        $grant = createGrantWithAgreementNumber($user, Carbon::create(2025, 12, 15));

        // Now it's January 2026 — different year
        $this->travelTo(Carbon::create(2026, 1, 10));

        Livewire::actingAs($user)
            ->test(Show::class, ['grant' => $grant])
            ->assertDontSeeText(__('page.grant-detail.revise-number-button'));
    });

    it('hides revise button for Polda user', function () {
        $this->travelTo(Carbon::create(2026, 1, 15));
        $user = createSatkerUserForRevise();
        $polda = User::find($user->unit->id_unit_atasan);
        $grant = createGrantWithAgreementNumber($user, Carbon::create(2026, 1, 15));

        $this->travelTo(Carbon::create(2026, 2, 10));

        Livewire::actingAs($polda)
            ->test(Show::class, ['grant' => $grant])
            ->assertDontSeeText(__('page.grant-detail.revise-number-button'));
    });

    it('revises the agreement number month successfully', function () {
        // Issued in January 2026
        $this->travelTo(Carbon::create(2026, 1, 15));
        $user = createSatkerUserForRevise();
        $grant = createGrantWithAgreementNumber($user, Carbon::create(2026, 1, 15));

        // Now it's March 2026
        $this->travelTo(Carbon::create(2026, 3, 10));

        Livewire::actingAs($user)
            ->test(Show::class, ['grant' => $grant])
            ->call('reviseAgreementNumberMonth')
            ->assertHasNoErrors();

        $grant->refresh();
        $latestNumbering = $grant->numberings
            ->where('tahapan', GrantStage::Agreement)
            ->first();

        expect((int) $latestNumbering->bulan)->toBe(3)
            ->and($latestNumbering->nomor)->toContain('/III/');
    });

    it('soft-deletes the old numbering when revising', function () {
        $this->travelTo(Carbon::create(2026, 1, 15));
        $user = createSatkerUserForRevise();
        $grant = createGrantWithAgreementNumber($user, Carbon::create(2026, 1, 15));

        $oldNumberingId = $grant->numberings
            ->where('tahapan', GrantStage::Agreement)
            ->first()
            ->id;

        $this->travelTo(Carbon::create(2026, 2, 10));

        Livewire::actingAs($user)
            ->test(Show::class, ['grant' => $grant])
            ->call('reviseAgreementNumberMonth');

        // Old numbering should be soft-deleted
        $this->assertSoftDeleted('penomoran_hibah', ['id' => $oldNumberingId]);

        // New numbering should exist
        $grant->refresh();
        $newNumbering = $grant->numberings
            ->where('tahapan', GrantStage::Agreement)
            ->first();

        expect($newNumbering->id)->not->toBe($oldNumberingId)
            ->and((int) $newNumbering->bulan)->toBe(2);
    });

    it('forbids non-owner Satker from revising', function () {
        $this->travelTo(Carbon::create(2026, 1, 15));
        $owner = createSatkerUserForRevise();
        $otherSatker = createSatkerUserForRevise();
        $grant = createGrantWithAgreementNumber($owner, Carbon::create(2026, 1, 15));

        $this->travelTo(Carbon::create(2026, 2, 10));

        Livewire::actingAs($otherSatker)
            ->test(Show::class, ['grant' => $grant])
            ->assertForbidden();
    });
});
