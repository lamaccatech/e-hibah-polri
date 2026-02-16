<?php

namespace App\Repositories;

use App\Enums\FileType;
use App\Enums\GrantGeneratedDocumentType;
use App\Enums\GrantStage;
use App\Models\Grant;
use App\Models\GrantDocument;
use App\Models\OrgUnitChief;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class GrantDocumentRepository
{
    public function __construct(private GrantDetailRepository $detailRepository) {}

    public function getActiveChief(Grant $grant): ?OrgUnitChief
    {
        return $grant->orgUnit->chiefs()
            ->where('sedang_menjabat', true)
            ->first();
    }

    /**
     * Persist a generated PDF: always create a new GrantDocument record to keep history.
     */
    public function persistDocument(
        Grant $grant,
        GrantGeneratedDocumentType $type,
        string $date,
        string $tempPath,
    ): GrantDocument {
        $document = $grant->documents()->create([
            'jenis_dokumen' => $type->value,
            'tanggal' => $date,
        ]);

        $uploadedFile = new UploadedFile($tempPath, $type->filename($grant), 'application/pdf', null, true);
        $document->attachFile($uploadedFile, FileType::GeneratedDocument);

        return $document;
    }

    /**
     * Get all documents grouped by type for the document history timeline.
     *
     * @return array<string, array{label: string, documents: Collection<int, GrantDocument>}>
     */
    public function getDocumentHistory(Grant $grant): array
    {
        $documents = $grant->documents()
            ->with('files')
            ->orderByDesc('created_at')
            ->get();

        $history = [];
        foreach (GrantGeneratedDocumentType::cases() as $type) {
            $typeDocs = $documents->where('jenis_dokumen', $type);
            $history[$type->slug()] = [
                'label' => $type->label(),
                'documents' => $typeDocs->values(),
            ];
        }

        return $history;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDocumentData(
        GrantGeneratedDocumentType $type,
        Grant $grant,
        string $date,
        OrgUnitChief $chief,
    ): array {
        return match ($type) {
            GrantGeneratedDocumentType::AssessmentDocument => $this->getAssessmentDocumentData($grant, $date, $chief),
            GrantGeneratedDocumentType::ProposalDocument => $this->getProposalDocumentData($grant, $date, $chief),
            GrantGeneratedDocumentType::ReadinessDocument => $this->getReadinessDocumentData($grant, $date, $chief),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function getAssessmentDocumentData(Grant $grant, string $date, OrgUnitChief $chief): array
    {
        $chapters = $this->detailRepository->getProposalChapters($grant);
        $chaptersByKey = $chapters->keyBy('judul');

        return [
            'grant' => $grant,
            'donor' => $grant->donor,
            'orgUnit' => $grant->orgUnit,
            'purposeChapter' => $chaptersByKey->get(\App\Enums\ProposalChapter::Purpose->value),
            'objectiveChapter' => $chaptersByKey->get(\App\Enums\ProposalChapter::Objective->value),
            'budgetPlans' => $this->detailRepository->getBudgetPlans($grant),
            'satkerAssessments' => $this->detailRepository->getSatkerAssessments($grant),
            'chief' => $chief,
            'documentDate' => $date,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getProposalDocumentData(Grant $grant, string $date, OrgUnitChief $chief): array
    {
        $planningNumber = $grant->numberings
            ->where('tahapan', GrantStage::Planning)
            ->first()?->nomor;

        return [
            'grant' => $grant,
            'orgUnit' => $grant->orgUnit,
            'chapters' => $this->detailRepository->getProposalChapters($grant),
            'budgetPlans' => $this->detailRepository->getBudgetPlans($grant),
            'activitySchedules' => $this->detailRepository->getActivitySchedules($grant),
            'planningNumber' => $planningNumber,
            'chief' => $chief,
            'documentDate' => $date,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getReadinessDocumentData(Grant $grant, string $date, OrgUnitChief $chief): array
    {
        return [
            'grant' => $grant,
            'donor' => $grant->donor,
            'orgUnit' => $grant->orgUnit,
            'satkerAssessments' => $this->detailRepository->getSatkerAssessments($grant),
            'poldaResults' => $this->detailRepository->getPoldaAssessmentResults($grant),
            'mabesResults' => $this->detailRepository->getMabesAssessmentResults($grant),
            'financeMinistrySubmission' => $grant->financeMinistrySubmission,
            'withdrawalPlans' => $grant->withdrawalPlans()->orderBy('nomor_urut')->get(),
            'locationAllocations' => $grant->locationAllocations()->get(),
            'chief' => $chief,
            'documentDate' => $date,
        ];
    }
}
