<?php

namespace App\Livewire\GrantPlanning;

use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class Index extends Component
{
    public function submit(int $grantId, GrantPlanningRepository $repository): void
    {
        $grant = $repository->findForUnit($grantId, auth()->user()->unit);

        if (! $repository->canSubmit($grant)) {
            $this->addError('submit', __('page.grant-planning.submit-incomplete'));

            return;
        }

        $repository->submitToPolda($grant);
    }

    public function render(GrantPlanningRepository $repository)
    {
        return view('livewire.grant-planning.index', [
            'grants' => $repository->allForUnit(auth()->user()->unit),
        ]);
    }
}
