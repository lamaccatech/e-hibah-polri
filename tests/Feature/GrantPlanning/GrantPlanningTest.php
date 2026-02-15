<?php

// SPEC: Grant Planning — Satker Steps 1-5
// This test file serves as executable specification for the grant planning journey.
// See specs/journeys/grant-planning.md for full journey spec.

use App\Enums\AssessmentAspect;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\ProposalChapter;
use App\Livewire\GrantPlanning\Assessment;
use App\Livewire\GrantPlanning\DonorInfo;
use App\Livewire\GrantPlanning\Index;
use App\Livewire\GrantPlanning\Initialize;
use App\Livewire\GrantPlanning\ProposalDocument;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createSatkerUserForGrantTest(?int $parentUserId = null): User
{
    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $parentUserId,
        ])
    );

    return $user;
}

function createMabesUserForGrantTest(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createGrantForUnit(User $user, array $overrides = []): Grant
{
    return $user->unit->grants()->create(
        Grant::factory()->withoutDonor()->planned()->raw($overrides)
    );
}

function createFullGrant(User $user): Grant
{
    $grant = $user->unit->grants()->create(
        Grant::factory()->planned()->raw()
    );

    // Add status history
    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
    ]);

    // Add proposal chapters
    $info = $grant->information()->create([
        'judul' => ProposalChapter::General->value,
        'tahapan' => GrantStage::Planning->value,
    ]);
    $info->contents()->create([
        'subjudul' => '',
        'isi' => 'Test content paragraph',
        'nomor_urut' => 1,
    ]);

    // Add budget plan
    $grant->budgetPlans()->create([
        'uraian' => 'Test item',
        'volume' => 1,
        'satuan' => 'unit',
        'harga_satuan' => 100000,
    ]);

    // Add schedule
    $grant->activitySchedules()->create([
        'uraian_kegiatan' => 'Test activity',
        'tanggal_mulai' => '2026-03-01',
        'tanggal_selesai' => '2026-06-01',
    ]);

    // Add assessment
    $statusHistory = $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::PlanningInitialized->value,
        'status_sesudah' => GrantStatus::CreatingPlanningAssessment->value,
    ]);
    $assessment = $statusHistory->assessments()->create([
        'judul' => 'Teknis',
        'aspek' => AssessmentAspect::Technical->value,
        'tahapan' => GrantStage::Planning->value,
    ]);
    $assessment->contents()->create([
        'subjudul' => '',
        'isi' => 'Assessment content',
        'nomor_urut' => 1,
    ]);

    return $grant;
}

// ============================================================
// Step 1 — Initialize
// ============================================================

describe('Grant Planning — Step 1 — Initialize', function () {
    it('allows Satker to access the create form', function () {
        $satker = createSatkerUserForGrantTest();

        $this->actingAs($satker)
            ->get(route('grant-planning.create'))
            ->assertSuccessful();
    });

    it('allows Satker to create a grant with activity name', function () {
        $satker = createSatkerUserForGrantTest();

        $this->actingAs($satker);

        Livewire::test(Initialize::class)
            ->set('activityName', 'Pengadaan Peralatan IT')
            ->call('save')
            ->assertHasNoErrors();

        $grant = Grant::where('nama_hibah', 'Pengadaan Peralatan IT')->first();
        expect($grant)->not->toBeNull();
        expect($grant->id_satuan_kerja)->toBe($satker->id);
        expect($grant->tahapan)->toBe(GrantStage::Planning);
    });

    it('creates status history with PlanningInitialized', function () {
        $satker = createSatkerUserForGrantTest();

        $this->actingAs($satker);

        Livewire::test(Initialize::class)
            ->set('activityName', 'Test Grant')
            ->call('save');

        $grant = Grant::where('nama_hibah', 'Test Grant')->first();
        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::PlanningInitialized);
    });

    it('redirects to donor info page', function () {
        $satker = createSatkerUserForGrantTest();

        $this->actingAs($satker);

        Livewire::test(Initialize::class)
            ->set('activityName', 'Test Grant')
            ->call('save')
            ->assertRedirect();

        $grant = Grant::where('nama_hibah', 'Test Grant')->first();
        expect($grant)->not->toBeNull();
    });

    it('validates activity name is required', function () {
        $satker = createSatkerUserForGrantTest();

        $this->actingAs($satker);

        Livewire::test(Initialize::class)
            ->set('activityName', '')
            ->call('save')
            ->assertHasErrors(['activityName' => 'required']);
    });
});

// ============================================================
// Step 2 — Donor Info
// ============================================================

describe('Grant Planning — Step 2 — Donor Info', function () {
    it('allows Satker to create a new donor and link to grant', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);

        $this->actingAs($satker);

        Livewire::test(DonorInfo::class, ['grant' => $grant])
            ->set('donorMode', 'new')
            ->set('name', 'PT Donor Baru')
            ->set('origin', 'Indonesia')
            ->set('address', 'Jl. Test No. 1')
            ->set('country', 'Indonesia')
            ->set('category', 'Swasta')
            ->call('save')
            ->assertHasNoErrors();

        $grant->refresh();
        expect($grant->id_pemberi_hibah)->not->toBeNull();
        expect($grant->donor->nama)->toBe('PT Donor Baru');
    });

    it('allows Satker to select an existing donor', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $donor = Donor::factory()->create();

        $this->actingAs($satker);

        Livewire::test(DonorInfo::class, ['grant' => $grant])
            ->set('donorMode', 'existing')
            ->set('selectedDonorId', $donor->id)
            ->call('save')
            ->assertHasNoErrors();

        $grant->refresh();
        expect($grant->id_pemberi_hibah)->toBe($donor->id);
    });

    it('creates status history with FillingDonorCandidate', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $donor = Donor::factory()->create();

        $this->actingAs($satker);

        Livewire::test(DonorInfo::class, ['grant' => $grant])
            ->set('selectedDonorId', $donor->id)
            ->call('save');

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::FillingDonorCandidate);
    });

    it('redirects to proposal document page', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $donor = Donor::factory()->create();

        $this->actingAs($satker);

        Livewire::test(DonorInfo::class, ['grant' => $grant])
            ->set('selectedDonorId', $donor->id)
            ->call('save')
            ->assertRedirect(route('grant-planning.proposal-document', $grant));
    });

    it('prevents accessing another unit grant', function () {
        $satker1 = createSatkerUserForGrantTest();
        $satker2 = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker1);

        $this->actingAs($satker2)
            ->get(route('grant-planning.donor', $grant))
            ->assertForbidden();
    });
});

// ============================================================
// Step 3 — Proposal Document
// ============================================================

describe('Grant Planning — Step 3 — Proposal Document', function () {
    it('allows Satker to save chapters with budget items and schedules', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $grant->update(['id_pemberi_hibah' => Donor::factory()->create()->id]);

        $this->actingAs($satker);

        $chapters = [];
        foreach (ProposalChapter::cases() as $chapter) {
            if (in_array($chapter, [ProposalChapter::ReceptionBasis, ProposalChapter::SupervisionMechanism])) {
                continue;
            }
            $prompts = $chapter->prompts();
            $count = max(count($prompts), 1);
            $chapters[$chapter->value] = array_fill(0, $count, 'Test content for '.$chapter->value.' that is long enough.');
        }

        Livewire::test(ProposalDocument::class, ['grant' => $grant])
            ->set('chapters', $chapters)
            ->set('budgetItems', [[
                'uraian' => 'Laptop',
                'volume' => '5',
                'satuan' => 'unit',
                'harga_satuan' => '15000000',
            ]])
            ->set('schedules', [[
                'uraian_kegiatan' => 'Pengadaan',
                'tanggal_mulai' => '2026-04-01',
                'tanggal_selesai' => '2026-06-30',
            ]])
            ->set('currency', 'IDR')
            ->call('save')
            ->assertHasNoErrors();

        $grant->refresh();
        expect($grant->information()->where('tahapan', GrantStage::Planning)->count())->toBe(10);
        expect($grant->budgetPlans()->count())->toBe(1);
        expect($grant->activitySchedules()->count())->toBe(1);
    });

    it('auto-calculates grant value from budget items', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $grant->update(['id_pemberi_hibah' => Donor::factory()->create()->id]);

        $this->actingAs($satker);

        $chapters = [];
        foreach (ProposalChapter::cases() as $chapter) {
            if (in_array($chapter, [ProposalChapter::ReceptionBasis, ProposalChapter::SupervisionMechanism])) {
                continue;
            }
            $prompts = $chapter->prompts();
            $count = max(count($prompts), 1);
            $chapters[$chapter->value] = array_fill(0, $count, 'Sufficient content to pass validation check.');
        }

        Livewire::test(ProposalDocument::class, ['grant' => $grant])
            ->set('chapters', $chapters)
            ->set('budgetItems', [
                ['uraian' => 'Item A', 'volume' => '2', 'satuan' => 'unit', 'harga_satuan' => '1000000'],
                ['uraian' => 'Item B', 'volume' => '3', 'satuan' => 'paket', 'harga_satuan' => '500000'],
            ])
            ->set('schedules', [[
                'uraian_kegiatan' => 'Test',
                'tanggal_mulai' => '2026-04-01',
                'tanggal_selesai' => '2026-06-30',
            ]])
            ->set('currency', 'IDR')
            ->call('save');

        $grant->refresh();
        // 2 * 1000000 + 3 * 500000 = 3500000
        expect($grant->nilai_hibah)->toBe('3500000.00');
    });

    it('creates status history with CreatingProposalDocument', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $grant->update(['id_pemberi_hibah' => Donor::factory()->create()->id]);

        $this->actingAs($satker);

        $chapters = [];
        foreach (ProposalChapter::cases() as $chapter) {
            if (in_array($chapter, [ProposalChapter::ReceptionBasis, ProposalChapter::SupervisionMechanism])) {
                continue;
            }
            $prompts = $chapter->prompts();
            $count = max(count($prompts), 1);
            $chapters[$chapter->value] = array_fill(0, $count, 'Sufficient content to pass validation check.');
        }

        Livewire::test(ProposalDocument::class, ['grant' => $grant])
            ->set('chapters', $chapters)
            ->set('budgetItems', [[
                'uraian' => 'Test', 'volume' => '1', 'satuan' => 'unit', 'harga_satuan' => '100000',
            ]])
            ->set('schedules', [[
                'uraian_kegiatan' => 'Test',
                'tanggal_mulai' => '2026-04-01',
                'tanggal_selesai' => '2026-06-30',
            ]])
            ->set('currency', 'IDR')
            ->call('save');

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::CreatingProposalDocument);
    });

    it('replaces existing data on re-save', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $grant->update(['id_pemberi_hibah' => Donor::factory()->create()->id]);

        // Create initial data
        $grant->budgetPlans()->create([
            'uraian' => 'Old item', 'volume' => 1, 'satuan' => 'unit', 'harga_satuan' => 50000,
        ]);

        $this->actingAs($satker);

        $chapters = [];
        foreach (ProposalChapter::cases() as $chapter) {
            if (in_array($chapter, [ProposalChapter::ReceptionBasis, ProposalChapter::SupervisionMechanism])) {
                continue;
            }
            $prompts = $chapter->prompts();
            $count = max(count($prompts), 1);
            $chapters[$chapter->value] = array_fill(0, $count, 'Sufficient content to pass validation check.');
        }

        Livewire::test(ProposalDocument::class, ['grant' => $grant])
            ->set('chapters', $chapters)
            ->set('budgetItems', [[
                'uraian' => 'New item', 'volume' => '2', 'satuan' => 'paket', 'harga_satuan' => '200000',
            ]])
            ->set('schedules', [[
                'uraian_kegiatan' => 'New activity',
                'tanggal_mulai' => '2026-04-01',
                'tanggal_selesai' => '2026-06-30',
            ]])
            ->set('currency', 'IDR')
            ->call('save');

        $grant->refresh();
        expect($grant->budgetPlans()->count())->toBe(1);
        expect($grant->budgetPlans()->first()->uraian)->toBe('New item');
    });

    it('validates required fields', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);

        $this->actingAs($satker);

        Livewire::test(ProposalDocument::class, ['grant' => $grant])
            ->set('budgetItems', [])
            ->call('save')
            ->assertHasErrors();
    });
});

// ============================================================
// Step 4 — Assessment
// ============================================================

describe('Grant Planning — Step 4 — Assessment', function () {
    it('allows Satker to save 4 mandatory aspects with paragraphs', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
        ]);

        $this->actingAs($satker);

        $mandatoryAspects = [];
        foreach (AssessmentAspect::cases() as $aspect) {
            $prompts = $aspect->prompts();
            $mandatoryAspects[$aspect->value] = array_fill(0, count($prompts), 'Assessment paragraph with enough content.');
        }

        Livewire::test(Assessment::class, ['grant' => $grant])
            ->set('mandatoryAspects', $mandatoryAspects)
            ->call('save')
            ->assertHasNoErrors();

        $assessments = $grant->statusHistory()
            ->latest('id')->first()
            ->assessments()
            ->where('tahapan', GrantStage::Planning)
            ->get();

        expect($assessments)->toHaveCount(4);
    });

    it('allows Satker to add optional custom aspects', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
        ]);

        $this->actingAs($satker);

        $mandatoryAspects = [];
        foreach (AssessmentAspect::cases() as $aspect) {
            $prompts = $aspect->prompts();
            $mandatoryAspects[$aspect->value] = array_fill(0, count($prompts), 'Assessment paragraph with enough content.');
        }

        Livewire::test(Assessment::class, ['grant' => $grant])
            ->set('mandatoryAspects', $mandatoryAspects)
            ->set('customAspects', [[
                'title' => 'Aspek Tambahan',
                'paragraphs' => ['Custom content paragraph with enough length.'],
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $assessments = $grant->statusHistory()
            ->latest('id')->first()
            ->assessments()
            ->where('tahapan', GrantStage::Planning)
            ->get();

        // 4 mandatory + 1 custom
        expect($assessments)->toHaveCount(5);
        expect($assessments->whereNull('aspek'))->toHaveCount(1);
    });

    it('creates status history with CreatingPlanningAssessment', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
        ]);

        $this->actingAs($satker);

        $mandatoryAspects = [];
        foreach (AssessmentAspect::cases() as $aspect) {
            $mandatoryAspects[$aspect->value] = array_fill(0, count($aspect->prompts()), 'Assessment paragraph with enough content.');
        }

        Livewire::test(Assessment::class, ['grant' => $grant])
            ->set('mandatoryAspects', $mandatoryAspects)
            ->call('save');

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::CreatingPlanningAssessment);
    });

    it('replaces existing assessments on re-save', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);
        $history = $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
        ]);

        // Create existing assessment
        $assessment = $history->assessments()->create([
            'judul' => 'Old',
            'aspek' => AssessmentAspect::Technical->value,
            'tahapan' => GrantStage::Planning->value,
        ]);
        $assessment->contents()->create([
            'subjudul' => '',
            'isi' => 'Old content',
            'nomor_urut' => 1,
        ]);

        $this->actingAs($satker);

        $mandatoryAspects = [];
        foreach (AssessmentAspect::cases() as $aspect) {
            $mandatoryAspects[$aspect->value] = array_fill(0, count($aspect->prompts()), 'New assessment content paragraph here.');
        }

        Livewire::test(Assessment::class, ['grant' => $grant])
            ->set('mandatoryAspects', $mandatoryAspects)
            ->call('save')
            ->assertHasNoErrors();

        // Should have exactly 4 assessments (the old ones replaced)
        $allAssessments = $grant->statusHistory()
            ->with(['assessments' => fn ($q) => $q->where('tahapan', GrantStage::Planning)])
            ->get()
            ->pluck('assessments')
            ->flatten();

        expect($allAssessments)->toHaveCount(4);
    });
});

// ============================================================
// Step 5 — Submit to Polda
// ============================================================

describe('Grant Planning — Step 5 — Submit to Polda', function () {
    it('allows Satker to submit a completed grant', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createFullGrant($satker);

        $this->actingAs($satker);

        Livewire::test(Index::class)
            ->call('submit', $grant->id)
            ->assertHasNoErrors();

        expect($grant->statusHistory()->latest('id')->first()->status_sesudah)
            ->toBe(GrantStatus::PlanningSubmittedToPolda);
    });

    it('prevents submission when steps are incomplete', function () {
        $satker = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker);

        $this->actingAs($satker);

        Livewire::test(Index::class)
            ->call('submit', $grant->id)
            ->assertHasErrors('submit');
    });
});

// ============================================================
// Access Control
// ============================================================

describe('Grant Planning — Access Control', function () {
    it('redirects non-Satker from grant planning index', function () {
        $mabes = createMabesUserForGrantTest();

        $this->actingAs($mabes)
            ->get(route('grant-planning.index'))
            ->assertRedirect(route('dashboard'));
    });

    it('redirects non-Satker from create form', function () {
        $mabes = createMabesUserForGrantTest();

        $this->actingAs($mabes)
            ->get(route('grant-planning.create'))
            ->assertRedirect(route('dashboard'));
    });

    it('prevents Satker from accessing another unit grant', function () {
        $satker1 = createSatkerUserForGrantTest();
        $satker2 = createSatkerUserForGrantTest();
        $grant = createGrantForUnit($satker1);

        $this->actingAs($satker2)
            ->get(route('grant-planning.donor', $grant))
            ->assertForbidden();
    });
});
