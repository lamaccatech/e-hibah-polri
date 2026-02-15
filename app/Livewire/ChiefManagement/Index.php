<?php

namespace App\Livewire\ChiefManagement;

use App\Repositories\ChiefRepository;
use Livewire\Component;

class Index extends Component
{
    public function assign(int $id, ChiefRepository $repository): void
    {
        $repository->assignActive(auth()->user()->unit, $id);
    }

    public function render(ChiefRepository $repository)
    {
        return view('livewire.chief-management.index', [
            'chiefs' => $repository->allForUnit(auth()->user()->unit),
        ]);
    }
}
