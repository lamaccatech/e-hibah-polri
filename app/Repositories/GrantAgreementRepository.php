<?php

namespace App\Repositories;

use App\Enums\FileType;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\GrantType;
use App\Enums\ProposalChapter;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\GrantNumbering;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class GrantAgreementRepository
{
    private ?HtmlSanitizer $sanitizer = null;

    private function sanitizer(): HtmlSanitizer
    {
        return $this->sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('h1')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('a', ['href', 'target', 'rel'])
                ->allowElement('sub')
                ->allowElement('sup')
                ->allowElement('mark')
                ->allowElement('code')
        );
    }

    private function sanitizeHtml(string $html): string
    {
        return $this->sanitizer()->sanitize($html);
    }

    /** @return Collection<int, Grant> */
    public function allForUnit(OrgUnit $unit): Collection
    {
        return $unit->grants()
            ->where('tahapan', GrantStage::Agreement)
            ->with(['donor', 'statusHistory'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function findPlanningGrantByNumber(string $letterNumber, OrgUnit $unit): ?Grant
    {
        $numbering = GrantNumbering::query()
            ->where('nomor', $letterNumber)
            ->where('tahapan', GrantStage::Planning)
            ->whereHas('grant', fn ($q) => $q->where('id_satuan_kerja', $unit->id_user))
            ->first();

        return $numbering?->grant;
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    public function createAgreement(OrgUnit $unit, string $activityName, string $letterNumber, array $objectives): Grant
    {
        return DB::transaction(function () use ($unit, $activityName, $letterNumber, $objectives): Grant {
            $grant = $unit->grants()->create([
                'nama_hibah' => $activityName,
                'jenis_hibah' => GrantType::Direct->value,
                'tahapan' => GrantStage::Agreement->value,
                'ada_usulan' => false,
                'nomor_surat_dari_calon_pemberi_hibah' => $letterNumber,
            ]);

            $grant->statusHistory()->create([
                'status_sesudah' => GrantStatus::FillingReceptionData->value,
                'keterangan' => "{$unit->nama_unit} mengisi data dasar penerimaan hibah dalam rangka kegiatan {$activityName}",
            ]);

            $this->saveObjectives($grant, $objectives);

            return $grant;
        });
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    public function transitionFromPlanning(Grant $grant, array $objectives): void
    {
        DB::transaction(function () use ($grant, $objectives): void {
            $grant->update([
                'tahapan' => GrantStage::Agreement->value,
            ]);

            $grant->statusHistory()->create([
                'status_sebelum' => GrantStatus::PlanningNumberIssued->value,
                'status_sesudah' => GrantStatus::FillingReceptionData->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} mengisi data dasar penerimaan hibah dalam rangka kegiatan {$grant->nama_hibah}",
            ]);

            $this->saveObjectives($grant, $objectives);
            $this->copyPlanningDataToAgreement($grant);
        });
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    public function updateReceptionBasis(Grant $grant, string $activityName, string $letterNumber, array $objectives): void
    {
        DB::transaction(function () use ($grant, $activityName, $letterNumber, $objectives): void {
            $grant->update([
                'nama_hibah' => $activityName,
                'nomor_surat_dari_calon_pemberi_hibah' => $letterNumber,
            ]);

            // Delete existing objectives for agreement stage and re-save
            $grant->information()
                ->where('tahapan', GrantStage::Agreement)
                ->where('judul', ProposalChapter::Objective->value)
                ->each(function ($info): void {
                    $info->contents()->forceDelete();
                    $info->forceDelete();
                });

            $this->saveObjectives($grant, $objectives);
        });
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    private function saveObjectives(Grant $grant, array $objectives): void
    {
        $info = $grant->information()->create([
            'judul' => ProposalChapter::Objective->value,
            'tahapan' => GrantStage::Agreement->value,
        ]);

        foreach ($objectives as $index => $objective) {
            $info->contents()->create([
                'subjudul' => $objective['purpose'],
                'isi' => $this->sanitizeHtml($objective['detail']),
                'nomor_urut' => $index + 1,
            ]);
        }
    }

    private function copyPlanningDataToAgreement(Grant $grant): void
    {
        $chaptersToCopy = [
            ProposalChapter::Purpose,
            ProposalChapter::Target,
            ProposalChapter::Benefit,
            ProposalChapter::ImplementationPlan,
            ProposalChapter::ReportingPlan,
            ProposalChapter::EvaluationPlan,
        ];

        foreach ($chaptersToCopy as $chapter) {
            $planningInfo = $grant->information()
                ->where('tahapan', GrantStage::Planning)
                ->where('judul', $chapter->value)
                ->first();

            if (! $planningInfo) {
                continue;
            }

            $agreementInfo = $grant->information()->create([
                'judul' => $planningInfo->judul,
                'tahapan' => GrantStage::Agreement->value,
            ]);

            $agreementInfo->contents()->createMany(
                $planningInfo->contents->map(fn ($c) => [
                    'subjudul' => $c->subjudul,
                    'isi' => $c->isi,
                    'nomor_urut' => $c->nomor_urut,
                ])->all()
            );
        }
    }

    public function linkDonor(Grant $grant, int $donorId): void
    {
        DB::transaction(function () use ($grant, $donorId): void {
            $grant->update(['id_pemberi_hibah' => $donorId]);

            $donor = Donor::find($donorId);

            $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::FillingDonorInfo->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} mengisi data pemberi hibah {$donor?->nama} untuk kegiatan {$grant->nama_hibah}",
            ]);

            // Auto-generate Purpose for direct grants (ada_usulan = false)
            if (! $grant->ada_usulan) {
                $this->generatePurpose($grant);
            }
        });
    }

    public function advanceToFillingDonorInfo(Grant $grant): void
    {
        $grant->statusHistory()->create([
            'status_sebelum' => $this->getLatestStatus($grant)?->value,
            'status_sesudah' => GrantStatus::FillingDonorInfo->value,
            'keterangan' => "{$grant->orgUnit->nama_unit} melanjutkan ke langkah data pemberi hibah untuk kegiatan {$grant->nama_hibah}",
        ]);
    }

    private function generatePurpose(Grant $grant): void
    {
        // Only generate if Purpose doesn't exist for agreement stage
        $existingPurpose = $grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->where('judul', ProposalChapter::Purpose->value)
            ->exists();

        if ($existingPurpose) {
            return;
        }

        $objectives = $grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->where('judul', ProposalChapter::Objective->value)
            ->with('contents')
            ->first();

        $purposeText = "<p>Kegiatan {$grant->nama_hibah}";
        if ($objectives && $objectives->contents->isNotEmpty()) {
            $objectiveNames = $objectives->contents
                ->sortBy('nomor_urut')
                ->pluck('subjudul')
                ->filter()
                ->implode(', ');
            if ($objectiveNames) {
                $purposeText .= " bertujuan untuk {$objectiveNames}";
            }
        }
        $purposeText .= '.</p>';

        $info = $grant->information()->create([
            'judul' => ProposalChapter::Purpose->value,
            'tahapan' => GrantStage::Agreement->value,
        ]);

        $info->contents()->create([
            'subjudul' => '',
            'isi' => $purposeText,
            'nomor_urut' => 1,
        ]);
    }

    /**
     * @param  array<int, array{judul: string, aspek: ?string, paragraphs: array<int, array{subjudul: string, isi: string}>}>  $aspects
     */
    public function saveAssessment(Grant $grant, array $aspects): void
    {
        DB::transaction(function () use ($grant, $aspects): void {
            // Delete existing agreement assessments
            $grant->statusHistory()
                ->get()
                ->each(function ($history): void {
                    $history->assessments()
                        ->where('tahapan', GrantStage::Agreement)
                        ->each(function ($assessment): void {
                            $assessment->contents()->forceDelete();
                            $assessment->forceDelete();
                        });
                });

            $statusHistory = $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::CreatingAgreementAssessment->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} membuat dokumen kajian hibah untuk kegiatan {$grant->nama_hibah}",
            ]);

            foreach ($aspects as $aspect) {
                $assessment = $statusHistory->assessments()->create([
                    'judul' => $aspect['judul'],
                    'aspek' => $aspect['aspek'],
                    'tahapan' => GrantStage::Agreement->value,
                ]);

                foreach ($aspect['paragraphs'] as $index => $content) {
                    $subjudul = '';
                    $isi = $content;

                    if (is_array($content)) {
                        $subjudul = $content['subjudul'] ?? '';
                        $isi = $content['isi'] ?? '';
                    }

                    if (trim($isi) === '') {
                        continue;
                    }

                    $assessment->contents()->create([
                        'subjudul' => $subjudul,
                        'isi' => $this->sanitizeHtml($isi),
                        'nomor_urut' => $index + 1,
                    ]);
                }
            }
        });
    }

    /**
     * @param  list<string>  $grantForms
     * @param  array<int, array{uraian: string, nilai: string}>  $budgetItems
     * @param  array<int, array{uraian: string, tanggal: string, nilai: string}>  $withdrawalPlans
     * @param  array<int, string>  $supervisionParagraphs
     */
    public function saveHarmonization(
        Grant $grant,
        array $grantForms,
        string $currency,
        array $budgetItems,
        array $withdrawalPlans,
        array $supervisionParagraphs,
    ): void {
        DB::transaction(function () use ($grant, $grantForms, $currency, $budgetItems, $withdrawalPlans, $supervisionParagraphs): void {
            // Delete existing data for re-save
            $grant->budgetPlans()->forceDelete();
            $grant->withdrawalPlans()->forceDelete();
            $grant->information()
                ->where('tahapan', GrantStage::Agreement)
                ->where('judul', ProposalChapter::SupervisionMechanism->value)
                ->each(function ($info): void {
                    $info->contents()->forceDelete();
                    $info->forceDelete();
                });

            // Save budget items and calculate total
            $totalValue = '0';
            foreach ($budgetItems as $index => $item) {
                $grant->budgetPlans()->create([
                    'nomor_urut' => $index + 1,
                    'uraian' => $item['uraian'],
                    'nilai' => $item['nilai'],
                ]);
                $totalValue = bcadd($totalValue, $item['nilai'], 2);
            }

            // Save withdrawal plans
            foreach ($withdrawalPlans as $index => $plan) {
                $grant->withdrawalPlans()->create([
                    'nomor_urut' => $index + 1,
                    'uraian' => $plan['uraian'],
                    'tanggal' => $plan['tanggal'],
                    'nilai' => $plan['nilai'],
                ]);
            }

            // Save supervision mechanism paragraphs
            $info = $grant->information()->create([
                'judul' => ProposalChapter::SupervisionMechanism->value,
                'tahapan' => GrantStage::Agreement->value,
            ]);

            foreach ($supervisionParagraphs as $index => $paragraph) {
                if (trim($paragraph) === '') {
                    continue;
                }

                $info->contents()->create([
                    'subjudul' => '',
                    'isi' => $this->sanitizeHtml($paragraph),
                    'nomor_urut' => $index + 1,
                ]);
            }

            // Update grant
            $grant->update([
                'bentuk_hibah' => implode(',', $grantForms),
                'mata_uang' => $currency,
                'nilai_hibah' => $totalValue,
            ]);

            $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::FillingHarmonization->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} mengisi data harmonisasi naskah perjanjian hibah untuk kegiatan {$grant->nama_hibah}",
            ]);
        });
    }

    /**
     * @param  array<string, array<int, array{subjudul: string, isi: string}>>  $chaptersData
     */
    public function saveAdditionalMaterials(Grant $grant, array $chaptersData): void
    {
        DB::transaction(function () use ($grant, $chaptersData): void {
            // Delete existing agreement-stage chapters for re-save
            $chapterKeys = array_keys($chaptersData);
            $grant->information()
                ->where('tahapan', GrantStage::Agreement)
                ->whereIn('judul', $chapterKeys)
                ->each(function ($info): void {
                    $info->contents()->forceDelete();
                    $info->forceDelete();
                });

            // Save each chapter
            foreach ($chaptersData as $chapterKey => $contents) {
                $info = $grant->information()->create([
                    'judul' => $chapterKey,
                    'tahapan' => GrantStage::Agreement->value,
                ]);

                foreach ($contents as $index => $content) {
                    if (trim($content['isi']) === '') {
                        continue;
                    }

                    $info->contents()->create([
                        'subjudul' => $content['subjudul'],
                        'isi' => $this->sanitizeHtml($content['isi']),
                        'nomor_urut' => $index + 1,
                    ]);
                }
            }

            $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::FillingAdditionalMaterials->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} mengisi materi tambahan kesiapan hibah untuk kegiatan {$grant->nama_hibah}",
            ]);
        });
    }

    /**
     * @param  array<int, array{title: string, paragraphs: array<int, string>}>  $customChapters
     */
    public function saveOtherMaterials(Grant $grant, array $customChapters): void
    {
        DB::transaction(function () use ($grant, $customChapters): void {
            $this->deleteCustomAgreementChapters($grant);

            foreach ($customChapters as $chapter) {
                $info = $grant->information()->create([
                    'judul' => $chapter['title'],
                    'tahapan' => GrantStage::Agreement->value,
                ]);

                foreach ($chapter['paragraphs'] as $index => $paragraph) {
                    if (trim($paragraph) === '') {
                        continue;
                    }

                    $info->contents()->create([
                        'subjudul' => '',
                        'isi' => $this->sanitizeHtml($paragraph),
                        'nomor_urut' => $index + 1,
                    ]);
                }
            }

            $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::FillingOtherMaterials->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} mengisi materi kesiapan hibah lainnya untuk kegiatan {$grant->nama_hibah}",
            ]);
        });
    }

    public function skipOtherMaterials(Grant $grant): void
    {
        $grant->statusHistory()->create([
            'status_sebelum' => $this->getLatestStatus($grant)?->value,
            'status_sesudah' => GrantStatus::FillingOtherMaterials->value,
            'keterangan' => "{$grant->orgUnit->nama_unit} melewati langkah materi kesiapan hibah lainnya untuk kegiatan {$grant->nama_hibah}",
        ]);
    }

    private function deleteCustomAgreementChapters(Grant $grant): void
    {
        $knownChapterValues = array_map(fn ($c) => $c->value, ProposalChapter::cases());

        $grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->whereNotIn('judul', $knownChapterValues)
            ->each(function ($info): void {
                $info->contents()->forceDelete();
                $info->forceDelete();
            });
    }

    public function saveDraftAgreement(Grant $grant, UploadedFile $draftFile): void
    {
        DB::transaction(function () use ($grant, $draftFile): void {
            $statusHistory = $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::UploadingDraftAgreement->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} mengupload draft naskah perjanjian hibah untuk kegiatan {$grant->nama_hibah}",
            ]);

            $statusHistory->attachFile($draftFile, FileType::DraftAgreement);
        });
    }

    public function isEditable(Grant $grant): bool
    {
        $latestStatus = $this->getLatestStatus($grant);

        return $latestStatus !== null && $latestStatus->isEditableBySatkerAgreement();
    }

    public function getLatestStatus(Grant $grant): ?GrantStatus
    {
        $latestHistory = $grant->statusHistory()
            ->latest('id')
            ->first();

        return $latestHistory?->status_sesudah;
    }
}
