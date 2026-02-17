<?php

namespace App\Livewire\AgreementReview;

use App\Models\Grant;
use App\Repositories\AgreementReviewRepository;
use Livewire\Component;

class Index extends Component
{
    public bool $showStartReviewModal = false;

    public ?int $grantToReviewId = null;

    public function confirmStartReview(int $grantId): void
    {
        $this->grantToReviewId = $grantId;
        $this->showStartReviewModal = true;
    }

    public function startReview(AgreementReviewRepository $repository): void
    {
        $grant = Grant::findOrFail($this->grantToReviewId);

        abort_unless($repository->canStartReview($grant), 403);

        $repository->startReview($grant, auth()->user()->unit);

        $this->redirect(route('agreement-review.review', $grant), navigate: true);
    }

    public function render(AgreementReviewRepository $repository)
    {
        $grants = $repository->allSubmittedToUnit(auth()->user()->unit);

        $reviewableIds = $grants
            ->filter(fn (Grant $grant) => $repository->canStartReview($grant))
            ->pluck('id')
            ->all();

        $underReviewIds = $grants
            ->filter(fn (Grant $grant) => $repository->isUnderReview($grant))
            ->pluck('id')
            ->all();

        return view('livewire.agreement-review.index', [
            'grants' => $grants,
            'reviewableIds' => $reviewableIds,
            'underReviewIds' => $underReviewIds,
        ]);
    }
}
