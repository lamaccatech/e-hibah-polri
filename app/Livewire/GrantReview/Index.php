<?php

namespace App\Livewire\GrantReview;

use App\Repositories\GrantReviewRepository;
use Livewire\Component;

class Index extends Component
{
    public function render(GrantReviewRepository $repository)
    {
        $grants = $repository->allSubmittedToUnit(auth()->user()->unit);

        return view('livewire.grant-review.index', [
            'grants' => $grants,
        ]);
    }
}
