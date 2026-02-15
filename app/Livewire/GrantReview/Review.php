<?php

namespace App\Livewire\GrantReview;

use App\Enums\AssessmentResult;
use App\Models\Grant;
use App\Repositories\GrantReviewRepository;
use Flux\Flux;
use Livewire\Component;

class Review extends Component
{
    public Grant $grant;

    public bool $showResultModal = false;

    public ?int $selectedAssessmentId = null;

    public ?string $result = null;

    public ?string $remarks = null;

    public function mount(): void
    {
        $repository = app(GrantReviewRepository::class);

        $unit = auth()->user()->unit;
        $childUnitUserIds = $unit->children()->pluck('id_user');

        abort_unless(
            $childUnitUserIds->contains($this->grant->id_satuan_kerja)
            && $repository->isUnderReview($this->grant),
            403
        );
    }

    public function openResultModal(int $assessmentId): void
    {
        $this->selectedAssessmentId = $assessmentId;
        $this->result = null;
        $this->remarks = null;
        $this->resetErrorBag();
        $this->showResultModal = true;
    }

    public function submitResult(GrantReviewRepository $repository): void
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

        Flux::toast(__('page.grant-review.result-saved'));

        if (! $repository->isUnderReview($this->grant)) {
            $this->redirect(route('grant-review.index'), navigate: true);
        }
    }

    public function render(GrantReviewRepository $repository)
    {
        $assessments = $repository->getReviewAssessments($this->grant);
        $satkerAssessments = $repository->getSatkerAssessments($this->grant);

        return view('livewire.grant-review.review', [
            'assessments' => $assessments,
            'satkerAssessments' => $satkerAssessments,
        ]);
    }
}
