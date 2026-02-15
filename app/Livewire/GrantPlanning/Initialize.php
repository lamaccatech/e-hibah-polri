<?php

namespace App\Livewire\GrantPlanning;

use App\Models\Grant;
use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class Initialize extends Component
{
    public ?Grant $grant = null;

    public string $activityName = '';

    public function mount(?Grant $grant = null): void
    {
        if ($grant?->exists) {
            $userUnit = auth()->user()->unit;
            abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
            abort_unless(app(GrantPlanningRepository::class)->isEditable($grant), 403);

            $this->grant = $grant;
            $this->activityName = str($grant->nama_hibah)->upper()->toString();
        }
    }

    protected function rules(): array
    {
        return [
            'activityName' => ['required', 'string', 'max:255'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'activityName' => __('page.grant-planning-create.label-activity-name'),
        ];
    }

    public function save(GrantPlanningRepository $repository): void
    {
        $validated = $this->validate();
        $validated['activityName'] = str($validated['activityName'])->upper()->toString();

        if ($this->grant) {
            $repository->updateGrantName($this->grant, $validated['activityName']);
            $grant = $this->grant;
        } else {
            $grant = $repository->createGrant(auth()->user()->unit, $validated['activityName']);
        }

        $this->redirect(route('grant-planning.donor', $grant), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-planning.create');
    }
}
