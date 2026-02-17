<?php

namespace App\Livewire\MabesAgreementReview;

use App\Models\Grant;
use App\Repositories\MabesAgreementReviewRepository;
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

    public function startReview(MabesAgreementReviewRepository $repository): void
    {
        $grant = Grant::findOrFail($this->grantToReviewId);

        abort_unless($repository->canStartReview($grant), 403);

        $repository->startReview($grant, auth()->user()->unit);

        $this->redirect(route('mabes-agreement-review.review', $grant), navigate: true);
    }

    public function render(MabesAgreementReviewRepository $repository)
    {
        $grants = $repository->allPoldaVerifiedAgreements();

        $reviewableIds = $grants
            ->filter(fn (Grant $grant) => $repository->canStartReview($grant))
            ->pluck('id')
            ->all();

        $underReviewIds = $grants
            ->filter(fn (Grant $grant) => $repository->isUnderReview($grant))
            ->pluck('id')
            ->all();

        return view('livewire.mabes-agreement-review.index', [
            'grants' => $grants,
            'reviewableIds' => $reviewableIds,
            'underReviewIds' => $underReviewIds,
        ]);
    }
}
