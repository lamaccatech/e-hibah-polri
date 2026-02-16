<?php

namespace App\Livewire\GrantDetail;

use App\Models\Grant;
use App\Repositories\GrantDetailRepository;
use Livewire\Component;

class Show extends Component
{
    public Grant $grant;

    public string $activeTab = 'grant-info';

    public function mount(Grant $grant, GrantDetailRepository $repository): void
    {
        $this->grant = $repository->findWithDetails($grant->id);

        abort_unless($repository->canView($this->grant, auth()->user()->unit), 403);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render(GrantDetailRepository $repository)
    {
        $data = ['grant' => $this->grant];

        if ($this->activeTab === 'grant-info') {
            $data['statusHistory'] = $this->grant->statusHistory;
        } elseif ($this->activeTab === 'proposal-info') {
            $data['chapters'] = $repository->getProposalChapters($this->grant);
            $data['budgetPlans'] = $repository->getBudgetPlans($this->grant);
            $data['activitySchedules'] = $repository->getActivitySchedules($this->grant);
        } elseif ($this->activeTab === 'assessment-info') {
            $data['satkerAssessments'] = $repository->getSatkerAssessments($this->grant);
            $data['poldaResults'] = $repository->getPoldaAssessmentResults($this->grant);
            $data['mabesResults'] = $repository->getMabesAssessmentResults($this->grant);
        }

        return view('livewire.grant-detail.show', $data);
    }
}
