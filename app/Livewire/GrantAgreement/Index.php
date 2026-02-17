<?php

namespace App\Livewire\GrantAgreement;

use App\Repositories\GrantAgreementRepository;
use Livewire\Component;

class Index extends Component
{
    public function render(GrantAgreementRepository $repository)
    {
        $grants = $repository->allForUnit(auth()->user()->unit);

        return view('livewire.grant-agreement.index', [
            'grants' => $grants,
        ]);
    }
}
