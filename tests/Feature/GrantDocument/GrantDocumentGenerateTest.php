<?php

use App\Enums\AssessmentAspect;
use App\Enums\FileType;
use App\Enums\GrantGeneratedDocumentType;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\ProposalChapter;
use App\Livewire\GrantDetail\Show;
use App\Livewire\GrantDocument\Generate;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\GrantDocument;
use App\Models\OrgUnit;
use App\Models\OrgUnitChief;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\LaravelPdf\Facades\Pdf;

function createSatkerUserForDocument(): User
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

function createPoldaUserForDocument(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createMabesUserForDocument(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createStoredDocument(Grant $grant, GrantGeneratedDocumentType $type = GrantGeneratedDocumentType::AssessmentDocument): GrantDocument
{
    $document = $grant->documents()->create([
        'jenis_dokumen' => $type->value,
        'tanggal' => '2026-03-15',
    ]);

    $tempPath = tempnam(sys_get_temp_dir(), 'pdf_');
    file_put_contents($tempPath, 'fake-pdf-content');

    $uploadedFile = new \Illuminate\Http\UploadedFile($tempPath, $type->filename($grant), 'application/pdf', null, true);
    $document->attachFile($uploadedFile, FileType::GeneratedDocument);

    @unlink($tempPath);

    return $document;
}

function createGrantWithDocumentData(User $satkerUser): Grant
{
    $donor = Donor::factory()->create();

    $grant = $satkerUser->unit->grants()->create(
        Grant::factory()->planned()->raw([
            'id_pemberi_hibah' => $donor->id,
            'nilai_hibah' => 100000000,
            'mata_uang' => 'IDR',
        ])
    );

    // Status history
    $grant->statusHistory()->create([
        'status_sesudah' => GrantStatus::PlanningInitialized->value,
        'keterangan' => 'Grant initialized',
    ]);

    // Proposal chapters
    $purposeChapter = $grant->information()->create([
        'judul' => ProposalChapter::Purpose->value,
        'tahapan' => GrantStage::Planning->value,
    ]);
    $purposeChapter->contents()->create([
        'subjudul' => 'Maksud',
        'isi' => 'Maksud dari kegiatan ini adalah...',
        'nomor_urut' => 1,
    ]);

    $generalChapter = $grant->information()->create([
        'judul' => ProposalChapter::General->value,
        'tahapan' => GrantStage::Planning->value,
    ]);
    $generalChapter->contents()->create([
        'subjudul' => 'Latar Belakang',
        'isi' => 'Latar belakang kegiatan ini adalah...',
        'nomor_urut' => 1,
    ]);

    // Budget plans
    $grant->budgetPlans()->create([
        'nomor_urut' => 1,
        'uraian' => 'Peralatan',
        'nilai' => 50000000,
    ]);

    // Activity schedules
    $grant->activitySchedules()->create([
        'uraian_kegiatan' => 'Pengadaan peralatan',
        'tanggal_mulai' => '2026-03-01',
        'tanggal_selesai' => '2026-06-30',
    ]);

    // Assessment (Satker)
    $assessmentHistory = $grant->statusHistory()->create([
        'status_sebelum' => GrantStatus::PlanningInitialized->value,
        'status_sesudah' => GrantStatus::CreatingPlanningAssessment->value,
        'keterangan' => 'Creating assessment',
    ]);

    foreach (AssessmentAspect::cases() as $aspect) {
        $assessment = $assessmentHistory->assessments()->create([
            'judul' => $aspect->label(),
            'aspek' => $aspect->value,
            'tahapan' => GrantStage::Planning->value,
        ]);
        $assessment->contents()->create([
            'subjudul' => $aspect->label(),
            'isi' => "Kajian aspek {$aspect->label()} oleh satker",
            'nomor_urut' => 1,
        ]);
    }

    return $grant;
}

// -------------------------------------------------------------------
// Access Control
// -------------------------------------------------------------------

describe('Grant Document — Access Control', function () {
    it('requires authentication', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->get(route('grant-document.generate', [$grant, 'assessment']))
            ->assertRedirect(route('login'));
    });

    it('returns 404 for invalid document type slug', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($satker)
            ->get(route('grant-document.generate', [$grant, 'invalid-type']))
            ->assertNotFound();
    });

    it('allows Satker to access own grant document page', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($satker)
            ->get(route('grant-document.generate', [$grant, 'assessment']))
            ->assertSuccessful();
    });

    it('denies Satker from accessing other unit grant', function () {
        $satker = createSatkerUserForDocument();
        $otherSatker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($otherSatker);

        $this->actingAs($satker)
            ->get(route('grant-document.generate', [$grant, 'assessment']))
            ->assertForbidden();
    });

    it('denies Polda from accessing grant document', function () {
        $satker = createSatkerUserForDocument();
        $polda = User::find($satker->unit->id_unit_atasan);
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($polda)
            ->get(route('grant-document.generate', [$grant, 'assessment']))
            ->assertForbidden();
    });

    it('denies Mabes from accessing grant document', function () {
        $mabes = createMabesUserForDocument();
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($mabes)
            ->get(route('grant-document.generate', [$grant, 'assessment']))
            ->assertForbidden();
    });
});

// -------------------------------------------------------------------
// Active Chief Requirement
// -------------------------------------------------------------------

describe('Grant Document — Active Chief Requirement', function () {
    it('shows warning when no active chief exists', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->assertSeeText(__('page.grant-document.no-chief-title'));
    });

    it('shows form when active chief exists', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->assertDontSeeText(__('page.grant-document.no-chief-title'))
            ->assertSeeText(__('page.grant-document.label-date'));
    });
});

// -------------------------------------------------------------------
// Preview
// -------------------------------------------------------------------

describe('Grant Document — Preview', function () {
    it('validates date before preview', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->call('preview')
            ->assertHasErrors(['documentDate' => 'required']);
    });

    it('shows preview for assessment document', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-03-15')
            ->call('preview')
            ->assertSet('showPreview', true)
            ->assertSeeText('Kajian Usulan Penerimaan Hibah');
    });

    it('shows preview for proposal document', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'proposal'])
            ->set('documentDate', '2026-03-15')
            ->call('preview')
            ->assertSet('showPreview', true)
            ->assertSeeText('Naskah Usulan Penerimaan Hibah Langsung');
    });

    it('shows preview for readiness document', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'readiness'])
            ->set('documentDate', '2026-03-15')
            ->call('preview')
            ->assertSet('showPreview', true)
            ->assertSeeText('Laporan Kesiapan Penerimaan Hibah Langsung');
    });

    it('toggles preview off when called again', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-03-15')
            ->call('preview')
            ->assertSet('showPreview', true)
            ->call('preview')
            ->assertSet('showPreview', false);
    });
});

// -------------------------------------------------------------------
// Download
// -------------------------------------------------------------------

describe('Grant Document — Download', function () {
    it('validates date before download', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->call('download')
            ->assertHasErrors(['documentDate' => 'required']);
    });

    it('downloads assessment document PDF', function () {
        Storage::fake();
        Pdf::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-03-15')
            ->call('download');

        Pdf::assertSaved(fn ($pdf, $path) => $pdf->viewName === 'pdf.assessment-document');
    });

    it('downloads proposal document PDF', function () {
        Storage::fake();
        Pdf::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'proposal'])
            ->set('documentDate', '2026-03-15')
            ->call('download');

        Pdf::assertSaved(fn ($pdf, $path) => $pdf->viewName === 'pdf.proposal-document');
    });

    it('downloads readiness document PDF', function () {
        Storage::fake();
        Pdf::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'readiness'])
            ->set('documentDate', '2026-03-15')
            ->call('download');

        Pdf::assertSaved(fn ($pdf, $path) => $pdf->viewName === 'pdf.readiness-document');
    });
});

// -------------------------------------------------------------------
// Document Persistence
// -------------------------------------------------------------------

describe('Grant Document — Persistence', function () {
    it('persists GrantDocument record and file on download', function () {
        Storage::fake();
        Pdf::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-03-15')
            ->call('download');

        $document = $grant->documents()->where('jenis_dokumen', GrantGeneratedDocumentType::AssessmentDocument->value)->first();
        expect($document)->not->toBeNull();
        expect($document->tanggal)->toBe('2026-03-15');

        $file = $document->getFirstFileByType(FileType::GeneratedDocument);
        expect($file)->not->toBeNull();
        expect($file->name)->toEndWith('.pdf');
    });

    it('keeps old document and creates new one on re-download', function () {
        Storage::fake();
        Pdf::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        // First download
        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-03-15')
            ->call('download');

        $firstDocument = $grant->documents()->oldest('id')->first();
        $firstFile = $firstDocument->getFirstFileByType(FileType::GeneratedDocument);

        // Second download with new date
        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-04-01')
            ->call('download');

        // Old document and file should still exist
        expect(\App\Models\File::find($firstFile->id))->not->toBeNull();

        // New document should be created
        $latestDocument = $grant->documents()->latest('id')->first();
        expect($latestDocument->tanggal)->toBe('2026-04-01');
        expect($latestDocument->id)->not->toBe($firstDocument->id);

        $newFile = $latestDocument->getFirstFileByType(FileType::GeneratedDocument);
        expect($newFile)->not->toBeNull();
        expect($newFile->id)->not->toBe($firstFile->id);
    });

    it('creates a new GrantDocument record per download', function () {
        Storage::fake();
        Pdf::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $satker->unit->chiefs()->create(OrgUnitChief::factory()->active()->raw());

        $this->actingAs($satker);

        // Download same type twice
        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-03-15')
            ->call('download');

        Livewire::test(Generate::class, ['grant' => $grant, 'type' => 'assessment'])
            ->set('documentDate', '2026-04-01')
            ->call('download');

        expect($grant->documents()->where('jenis_dokumen', GrantGeneratedDocumentType::AssessmentDocument->value)->count())->toBe(2);
    });
});

// -------------------------------------------------------------------
// Stored Document Download Route
// -------------------------------------------------------------------

describe('Grant Document — Download Route', function () {
    it('requires authentication for download route', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        Storage::fake();
        $document = createStoredDocument($grant);

        $this->get(route('grant-document.download', [$grant, $document]))
            ->assertRedirect(route('login'));
    });

    it('allows Satker to download own stored document', function () {
        Storage::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $document = createStoredDocument($grant);

        $this->actingAs($satker)
            ->get(route('grant-document.download', [$grant, $document]))
            ->assertSuccessful();
    });

    it('allows Polda to download child unit stored document', function () {
        Storage::fake();

        $satker = createSatkerUserForDocument();
        $polda = User::find($satker->unit->id_unit_atasan);
        $grant = createGrantWithDocumentData($satker);
        $document = createStoredDocument($grant);

        $this->actingAs($polda)
            ->get(route('grant-document.download', [$grant, $document]))
            ->assertSuccessful();
    });

    it('allows Mabes to download any stored document', function () {
        Storage::fake();

        $mabes = createMabesUserForDocument();
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $document = createStoredDocument($grant);

        $this->actingAs($mabes)
            ->get(route('grant-document.download', [$grant, $document]))
            ->assertSuccessful();
    });

    it('denies Satker from downloading other unit stored document', function () {
        Storage::fake();

        $satker = createSatkerUserForDocument();
        $otherSatker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($otherSatker);
        $document = createStoredDocument($grant);

        $this->actingAs($satker)
            ->get(route('grant-document.download', [$grant, $document]))
            ->assertForbidden();
    });

    it('denies Polda from downloading non-child unit stored document', function () {
        Storage::fake();

        $polda = createPoldaUserForDocument();
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $document = createStoredDocument($grant);

        $this->actingAs($polda)
            ->get(route('grant-document.download', [$grant, $document]))
            ->assertForbidden();
    });

    it('returns 404 if document has no generated file', function () {
        Storage::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $document = $grant->documents()->create([
            'jenis_dokumen' => GrantGeneratedDocumentType::AssessmentDocument->value,
            'tanggal' => '2026-03-15',
        ]);

        $this->actingAs($satker)
            ->get(route('grant-document.download', [$grant, $document]))
            ->assertNotFound();
    });

    it('returns 404 if document does not belong to grant', function () {
        Storage::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        $otherGrant = createGrantWithDocumentData($satker);
        $document = createStoredDocument($otherGrant);

        $this->actingAs($satker)
            ->get(route('grant-document.download', [$grant, $document]))
            ->assertNotFound();
    });
});

// -------------------------------------------------------------------
// Grant Detail — Generate Button for Satker
// -------------------------------------------------------------------

describe('Grant Document — Detail Page Generate Button', function () {
    it('shows generate dropdown for Satker', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText(__('page.grant-detail.generate-document'));
    });

    it('does not show generate dropdown for Polda', function () {
        $satker = createSatkerUserForDocument();
        $polda = User::find($satker->unit->id_unit_atasan);
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($polda);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertDontSeeText(__('page.grant-detail.generate-document'));
    });
});

// -------------------------------------------------------------------
// Grant Detail — Document History Tab
// -------------------------------------------------------------------

describe('Grant Document — Document History Tab', function () {
    it('shows document history tab for Satker', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText(__('page.grant-detail.tab-document-history'));
    });

    it('shows document history tab for Polda', function () {
        $satker = createSatkerUserForDocument();
        $polda = User::find($satker->unit->id_unit_atasan);
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($polda);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText(__('page.grant-detail.tab-document-history'));
    });

    it('shows document history tab for Mabes', function () {
        $mabes = createMabesUserForDocument();
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($mabes);

        Livewire::test(Show::class, ['grant' => $grant])
            ->assertSeeText(__('page.grant-detail.tab-document-history'));
    });

    it('shows empty state when no documents generated', function () {
        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'document-history')
            ->assertSeeText(__('page.grant-detail.no-document-generated'));
    });

    it('shows generated documents in timeline', function () {
        Storage::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        createStoredDocument($grant, GrantGeneratedDocumentType::AssessmentDocument);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'document-history')
            ->assertSeeText(GrantGeneratedDocumentType::AssessmentDocument->label())
            ->assertSeeText('2026-03-15');
    });

    it('shows multiple generations of same type in timeline', function () {
        Storage::fake();

        $satker = createSatkerUserForDocument();
        $grant = createGrantWithDocumentData($satker);
        createStoredDocument($grant, GrantGeneratedDocumentType::AssessmentDocument);

        // Create second document of same type with different date
        $document2 = $grant->documents()->create([
            'jenis_dokumen' => GrantGeneratedDocumentType::AssessmentDocument->value,
            'tanggal' => '2026-04-01',
        ]);
        $tempPath = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempPath, 'fake-pdf-content-2');
        $uploadedFile = new \Illuminate\Http\UploadedFile($tempPath, 'test.pdf', 'application/pdf', null, true);
        $document2->attachFile($uploadedFile, FileType::GeneratedDocument);
        @unlink($tempPath);

        $this->actingAs($satker);

        Livewire::test(Show::class, ['grant' => $grant])
            ->call('switchTab', 'document-history')
            ->assertSeeText('2026-03-15')
            ->assertSeeText('2026-04-01');
    });
});
