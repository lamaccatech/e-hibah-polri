<?php

namespace App\Livewire\GrantDetail;

use App\Enums\GrantStage;
use App\Enums\UnitLevel;
use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use App\Repositories\GrantDetailRepository;
use App\Repositories\GrantDocumentRepository;
use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class Show extends Component
{
    public Grant $grant;

    public string $activeTab = 'grant-info';

    public bool $showProposal = false;

    public function mount(Grant $grant, GrantDetailRepository $repository): void
    {
        $this->grant = $repository->findWithDetails($grant->id);

        abort_unless($repository->canView($this->grant, auth()->user()->unit), 403);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function toggleShowProposal(): void
    {
        $this->showProposal = ! $this->showProposal;

        // Reset to a safe tab if current tab is a proposal-only tab that's being hidden
        if (! $this->showProposal && in_array($this->activeTab, ['proposal-info', 'assessment-info', 'document-history'])) {
            $this->activeTab = 'grant-info';
        }
    }

    public function render(
        GrantDetailRepository $repository,
        GrantDocumentRepository $documentRepository,
        GrantPlanningRepository $planningRepository,
        GrantAgreementRepository $agreementRepository,
    ) {
        $isAgreementStage = $this->grant->tahapan === GrantStage::Agreement;
        $hasProposal = (bool) $this->grant->ada_usulan;

        $canUploadSigned = auth()->user()->unit->level_unit === UnitLevel::SatuanKerja
            && $this->grant->id_satuan_kerja === auth()->user()->unit->id_user
            && $agreementRepository->canUploadSignedAgreement($this->grant);

        $canSubmitSehati = auth()->user()->unit->level_unit === UnitLevel::SatuanKerja
            && $this->grant->id_satuan_kerja === auth()->user()->unit->id_user
            && $agreementRepository->canSubmitSehati($this->grant);

        $data = [
            'grant' => $this->grant,
            'isAgreementStage' => $isAgreementStage,
            'hasProposal' => $hasProposal,
            'showProposal' => $this->showProposal,
            'canUploadSigned' => $canUploadSigned,
            'canSubmitSehati' => $canSubmitSehati,
        ];

        if ($this->activeTab === 'grant-info') {
            $data['statusHistory'] = $this->grant->statusHistory;
            $data['uploadedFiles'] = $repository->getUploadedFiles($this->grant);
        } elseif ($this->activeTab === 'proposal-info') {
            $data['chapters'] = $repository->getProposalChapters($this->grant);
            $data['budgetPlans'] = $repository->getBudgetPlans($this->grant);
            $data['activitySchedules'] = $repository->getActivitySchedules($this->grant);
        } elseif ($this->activeTab === 'assessment-info') {
            $data['satkerAssessments'] = $repository->getSatkerAssessments($this->grant);
            $data['poldaResults'] = $repository->getPoldaAssessmentResults($this->grant);
            $data['mabesResults'] = $repository->getMabesAssessmentResults($this->grant);
            $data['canEditAssessment'] = auth()->user()->unit->level_unit === UnitLevel::SatuanKerja
                && $planningRepository->isEditable($this->grant);
        } elseif ($this->activeTab === 'agreement-info') {
            $data['agreementChapters'] = $repository->getAgreementChapters($this->grant);
            $data['budgetPlans'] = $repository->getBudgetPlans($this->grant);
            $data['withdrawalPlans'] = $repository->getWithdrawalPlans($this->grant);
            $data['activitySchedules'] = $repository->getActivitySchedules($this->grant);
        } elseif ($this->activeTab === 'agreement-assessment') {
            $data['satkerAgreementAssessments'] = $repository->getSatkerAgreementAssessments($this->grant);
            $data['poldaAgreementResults'] = $repository->getPoldaAgreementAssessmentResults($this->grant);
            $data['mabesAgreementResults'] = $repository->getMabesAgreementAssessmentResults($this->grant);
            $data['canEditAssessment'] = auth()->user()->unit->level_unit === UnitLevel::SatuanKerja
                && $agreementRepository->isEditable($this->grant);
        } elseif ($this->activeTab === 'document-history') {
            $data['documentHistory'] = $documentRepository->getDocumentHistory($this->grant);
        }

        return view('livewire.grant-detail.show', $data);
    }
}
