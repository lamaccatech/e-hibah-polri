<?php

namespace App\Livewire\MabesGrantReview;

use App\Enums\AssessmentResult;
use App\Models\Grant;
use App\Repositories\MabesGrantReviewRepository;
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
        $repository = app(MabesGrantReviewRepository::class);

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

    public function submitResult(MabesGrantReviewRepository $repository): void
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

        Flux::toast(__('page.mabes-grant-review.result-saved'));

        if (! $repository->isUnderReview($this->grant)) {
            $this->redirect(route('mabes-grant-review.index'), navigate: true);
        }
    }

    public function render(MabesGrantReviewRepository $repository)
    {
        $assessments = $repository->getReviewAssessments($this->grant);
        $satkerAssessments = $repository->getSatkerAssessments($this->grant);
        $poldaAssessments = $repository->getPoldaAssessments($this->grant);

        return view('livewire.mabes-grant-review.review', [
            'assessments' => $assessments,
            'satkerAssessments' => $satkerAssessments,
            'poldaAssessments' => $poldaAssessments,
        ]);
    }
}
