<?php

namespace App\Livewire\GrantPlanning;

use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class Index extends Component
{
    public bool $showSubmitModal = false;

    public ?int $grantToSubmit = null;

    public function confirmSubmit(int $grantId): void
    {
        $this->grantToSubmit = $grantId;
        $this->showSubmitModal = true;
    }

    public function submit(GrantPlanningRepository $repository): void
    {
        $grant = $repository->findForUnit($this->grantToSubmit, auth()->user()->unit);

        if (! $repository->canSubmit($grant)) {
            $this->addError('submit', __('page.grant-planning.submit-incomplete'));
            $this->showSubmitModal = false;
            $this->grantToSubmit = null;

            return;
        }

        $repository->submitToPolda($grant);

        $this->showSubmitModal = false;
        $this->grantToSubmit = null;
    }

    public function render(GrantPlanningRepository $repository)
    {
        $grants = $repository->allForUnit(auth()->user()->unit);
        $submittableIds = $grants->filter(fn ($grant) => $repository->canSubmit($grant))->pluck('id')->all();
        $editableIds = $grants->filter(fn ($grant) => $repository->isEditable($grant))->pluck('id')->all();

        return view('livewire.grant-planning.index', [
            'grants' => $grants,
            'submittableIds' => $submittableIds,
            'editableIds' => $editableIds,
        ]);
    }
}
