<?php

namespace App\Livewire\GrantPlanning;

use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class Initialize extends Component
{
    public string $activityName = '';

    protected function rules(): array
    {
        return [
            'activityName' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(GrantPlanningRepository $repository): void
    {
        $validated = $this->validate();

        $grant = $repository->createGrant(auth()->user()->unit, $validated['activityName']);

        $this->redirect(route('grant-planning.donor', $grant), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-planning.create');
    }
}
