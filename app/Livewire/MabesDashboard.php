<?php

namespace App\Livewire;

use App\Repositories\MabesDashboardRepository;
use Livewire\Component;

class MabesDashboard extends Component
{
    public function render(MabesDashboardRepository $repository)
    {
        return view('livewire.mabes-dashboard', [
            'counts' => $repository->getCounts(),
            'realization' => $repository->getRealization(),
            'yearlyTrend' => $repository->getYearlyTrend(),
        ]);
    }
}
