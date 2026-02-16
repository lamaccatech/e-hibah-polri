<?php

namespace App\Livewire\GrantPlanning;

use App\Enums\AssessmentAspect;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Models\Grant;
use App\Models\GrantAssessment;
use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class Assessment extends Component
{
    public Grant $grant;

    /** @var array<string, array<int, string>> Keyed by AssessmentAspect value */
    public array $mandatoryAspects = [];

    /** @var array<int, array{title: string, paragraphs: array<int, string>}> */
    public array $customAspects = [];

    /** @var array<string, array{result: string, remarks: ?string}> Keyed by AssessmentAspect value */
    public array $reviewFeedback = [];

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantPlanningRepository::class)->isEditable($grant), 403);

        $this->grant = $grant;
        $this->initializeAspects();
        $this->loadExistingData();
        $this->loadReviewFeedback();
    }

    protected function rules(): array
    {
        $rules = [];

        foreach (AssessmentAspect::cases() as $aspect) {
            $prompts = $aspect->prompts();
            foreach ($prompts as $i => $prompt) {
                $rules["mandatoryAspects.{$aspect->value}.{$i}"] = ['required', 'string', 'min:10'];
            }
        }

        foreach ($this->customAspects as $i => $aspect) {
            $rules["customAspects.{$i}.title"] = ['required', 'string', 'max:255'];
            foreach ($aspect['paragraphs'] as $j => $paragraph) {
                $rules["customAspects.{$i}.paragraphs.{$j}"] = ['required', 'string', 'min:10'];
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attributes = [
            'customAspects.*.title' => __('page.grant-planning-assessment.label-aspect-title'),
            'customAspects.*.paragraphs.*' => __('page.grant-planning-assessment.add-paragraph'),
        ];

        foreach (AssessmentAspect::cases() as $aspect) {
            foreach ($aspect->prompts() as $i => $prompt) {
                $attributes["mandatoryAspects.{$aspect->value}.{$i}"] = $aspect->label();
            }
        }

        return $attributes;
    }

    public function addCustomAspect(): void
    {
        $this->customAspects[] = [
            'title' => '',
            'paragraphs' => [''],
        ];
    }

    public function removeCustomAspect(int $index): void
    {
        unset($this->customAspects[$index]);
        $this->customAspects = array_values($this->customAspects);
    }

    public function addCustomParagraph(int $aspectIndex): void
    {
        $this->customAspects[$aspectIndex]['paragraphs'][] = '';
    }

    public function removeCustomParagraph(int $aspectIndex, int $paragraphIndex): void
    {
        unset($this->customAspects[$aspectIndex]['paragraphs'][$paragraphIndex]);
        $this->customAspects[$aspectIndex]['paragraphs'] = array_values($this->customAspects[$aspectIndex]['paragraphs']);
    }

    public function save(GrantPlanningRepository $repository): void
    {
        $this->validate();

        $aspects = [];

        // Mandatory aspects — store prompt text as subjudul
        foreach (AssessmentAspect::cases() as $aspect) {
            $prompts = $aspect->prompts();
            $aspects[] = [
                'judul' => $aspect->label(),
                'aspek' => $aspect->value,
                'paragraphs' => array_map(
                    fn ($content, $i) => ['subjudul' => $prompts[$i] ?? '', 'isi' => $content],
                    $this->mandatoryAspects[$aspect->value],
                    array_keys($this->mandatoryAspects[$aspect->value]),
                ),
            ];
        }

        // Custom aspects
        foreach ($this->customAspects as $custom) {
            $aspects[] = [
                'judul' => $custom['title'],
                'aspek' => null,
                'paragraphs' => array_map(
                    fn ($content) => ['subjudul' => '', 'isi' => $content],
                    $custom['paragraphs'],
                ),
            ];
        }

        $repository->saveAssessment($this->grant, $aspects);

        $this->redirect(route('grant-planning.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-planning.assessment', [
            'aspectCases' => AssessmentAspect::cases(),
            'reviewFeedback' => $this->reviewFeedback,
        ]);
    }

    private function initializeAspects(): void
    {
        foreach (AssessmentAspect::cases() as $aspect) {
            $prompts = $aspect->prompts();
            $this->mandatoryAspects[$aspect->value] = array_fill(0, count($prompts), '');
        }
    }

    private function loadReviewFeedback(): void
    {
        $latestStatus = $this->grant->statusHistory()
            ->latest('id')
            ->first()
            ?->status_sesudah;

        if (! $latestStatus?->isRevisionRequested()) {
            return;
        }

        // Find the review that led to the revision request
        $reviewStatus = match ($latestStatus) {
            GrantStatus::PoldaRequestedPlanningRevision => GrantStatus::PoldaReviewingPlanning,
            GrantStatus::MabesRequestedPlanningRevision => GrantStatus::MabesReviewingPlanning,
            default => null,
        };

        if (! $reviewStatus) {
            return;
        }

        $reviewHistory = $this->grant->statusHistory()
            ->where('status_sesudah', $reviewStatus)
            ->latest('id')
            ->first();

        if (! $reviewHistory) {
            return;
        }

        $reviewHistory->assessments()
            ->with('result')
            ->get()
            ->each(function (GrantAssessment $assessment): void {
                if ($assessment->aspek && $assessment->result) {
                    $this->reviewFeedback[$assessment->aspek->value] = [
                        'result' => $assessment->result->rekomendasi->value,
                        'remarks' => $assessment->result->keterangan,
                    ];
                }
            });
    }

    private function loadExistingData(): void
    {
        $assessmentHistory = $this->grant->statusHistory()
            ->where('status_sesudah', GrantStatus::CreatingPlanningAssessment)
            ->latest('id')
            ->first();

        if (! $assessmentHistory) {
            return;
        }

        $existingAssessments = $assessmentHistory->assessments()
            ->where('tahapan', GrantStage::Planning)
            ->with('contents')
            ->get();

        if ($existingAssessments->isEmpty()) {
            return;
        }

        foreach ($existingAssessments as $assessment) {
            $contents = $assessment->contents->sortBy('nomor_urut')->pluck('isi')->values()->all();

            if ($assessment->aspek !== null) {
                $this->mandatoryAspects[$assessment->aspek->value] = $contents;
            } else {
                $this->customAspects[] = [
                    'title' => $assessment->judul,
                    'paragraphs' => $contents,
                ];
            }
        }
    }
}
