<?php

namespace App\Livewire\MabesGrantReview;

use App\Models\Grant;
use App\Repositories\MabesGrantReviewRepository;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showStartReviewModal = false;

    public ?int $grantToReviewId = null;

    public function confirmStartReview(int $grantId): void
    {
        $this->grantToReviewId = $grantId;
        $this->showStartReviewModal = true;
    }

    public function startReview(MabesGrantReviewRepository $repository): void
    {
        $grant = Grant::findOrFail($this->grantToReviewId);

        abort_unless($repository->canStartReview($grant), 403);

        $repository->startReview($grant, auth()->user()->unit);

        $this->redirect(route('mabes-grant-review.review', $grant), navigate: true);
    }

    public function render(MabesGrantReviewRepository $repository)
    {
        $grants = $repository->allPoldaVerifiedGrants();

        $reviewableIds = $grants->getCollection()
            ->filter(fn (Grant $grant) => $repository->canStartReview($grant))
            ->pluck('id')
            ->all();

        $underReviewIds = $grants->getCollection()
            ->filter(fn (Grant $grant) => $repository->isUnderReview($grant))
            ->pluck('id')
            ->all();

        return view('livewire.mabes-grant-review.index', [
            'grants' => $grants,
            'reviewableIds' => $reviewableIds,
            'underReviewIds' => $underReviewIds,
        ]);
    }
}
