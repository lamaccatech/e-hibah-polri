<?php

namespace App\Livewire\GrantAgreement;

use App\Repositories\GrantAgreementRepository;
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

    public function submit(GrantAgreementRepository $repository): void
    {
        $grant = $repository->findForUnit($this->grantToSubmit, auth()->user()->unit);

        if (! $repository->canSubmit($grant)) {
            $this->addError('submit', __('page.grant-agreement.submit-incomplete'));
            $this->showSubmitModal = false;
            $this->grantToSubmit = null;

            return;
        }

        $repository->submitToPolda($grant);

        $this->showSubmitModal = false;
        $this->grantToSubmit = null;
    }

    public function render(GrantAgreementRepository $repository)
    {
        $grants = $repository->allForUnit(auth()->user()->unit);
        $submittableIds = $grants->filter(fn ($grant) => $repository->canSubmit($grant))->pluck('id')->all();
        $editableIds = $grants->filter(fn ($grant) => $repository->isEditable($grant))->pluck('id')->all();

        return view('livewire.grant-agreement.index', [
            'grants' => $grants,
            'submittableIds' => $submittableIds,
            'editableIds' => $editableIds,
        ]);
    }
}
