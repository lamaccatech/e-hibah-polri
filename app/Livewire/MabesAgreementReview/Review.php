<?php

namespace App\Livewire\MabesAgreementReview;

use App\Enums\AssessmentResult;
use App\Models\Grant;
use App\Repositories\MabesAgreementReviewRepository;
use Flux\Flux;
use Livewire\Component;

class Review extends Component
{
    public Grant $grant;

    public bool $showResultModal = false;

    public ?int $selectedAssessmentId = null;

    public ?string $selectedAspectLabel = null;

    public ?string $result = null;

    public ?string $remarks = null;

    public function mount(): void
    {
        $repository = app(MabesAgreementReviewRepository::class);

        abort_unless($repository->isUnderReview($this->grant), 403);
    }

    public function openResultModal(int $assessmentId, string $aspectLabel): void
    {
        $this->selectedAssessmentId = $assessmentId;
        $this->selectedAspectLabel = $aspectLabel;
        $this->result = null;
        $this->remarks = null;
        $this->resetErrorBag();
        $this->showResultModal = true;
    }

    public function submitResult(MabesAgreementReviewRepository $repository): void
    {
        $this->validate([
            'result' => ['required', 'in:'.implode(',', array_column(AssessmentResult::cases(), 'value'))],
            'remarks' => ['nullable', 'required_unless:result,'.AssessmentResult::Fulfilled->value, 'string'],
        ]);

        $assessment = $repository->findAssessmentForGrant($this->selectedAssessmentId, $this->grant);

        abort_if($assessment->result !== null, 422);

        $repository->submitAspectResult(
            $assessment,
            auth()->user()->unit,
            AssessmentResult::from($this->result),
            $this->remarks,
        );

        $this->showResultModal = false;
        $this->selectedAssessmentId = null;
        $this->result = null;
        $this->remarks = null;

        Flux::toast(__('page.mabes-agreement-review.result-saved'));

        if (! $repository->isUnderReview($this->grant)) {
            $this->redirect(route('mabes-agreement-review.index'), navigate: true);
        }
    }

    public function render(MabesAgreementReviewRepository $repository)
    {
        $assessments = $repository->getReviewAssessments($this->grant);
        $satkerAssessments = $repository->getSatkerAssessments($this->grant);
        $poldaAssessments = $repository->getPoldaAssessments($this->grant);

        return view('livewire.mabes-agreement-review.review', [
            'assessments' => $assessments,
            'satkerAssessments' => $satkerAssessments,
            'poldaAssessments' => $poldaAssessments,
        ]);
    }
}
