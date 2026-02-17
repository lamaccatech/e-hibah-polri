<?php

namespace App\Livewire;

use App\Repositories\PoldaDashboardRepository;
use Livewire\Component;
use Livewire\WithPagination;

class PoldaDashboard extends Component
{
    use WithPagination;

    public function render(PoldaDashboardRepository $repository)
    {
        $unit = auth()->user()->unit;

        return view('livewire.polda-dashboard', [
            'counts' => $repository->getCounts($unit),
            'inbox' => $repository->getInbox($unit),
        ]);
    }
}
