<?php

namespace App\Livewire\GrantAgreement;

use App\Enums\AssessmentAspect;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Illuminate\View\View;
use Livewire\Component;

class Assessment extends Component
{
    public Grant $grant;

    /** @var array<string, array<int, string>> Keyed by AssessmentAspect value */
    public array $mandatoryAspects = [];

    /** @var array<int, array{title: string, paragraphs: array<int, string>}> */
    public array $customAspects = [];

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantAgreementRepository::class)->isEditable($grant), 403);

        $this->grant = $grant;
        $this->initializeAspects();
        $this->loadExistingData();
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

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        $aspects = [];

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

        $this->redirect(route('grant-agreement.harmonization', $this->grant), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.grant-agreement.assessment', [
            'aspectCases' => AssessmentAspect::cases(),
        ]);
    }

    private function initializeAspects(): void
    {
        foreach (AssessmentAspect::cases() as $aspect) {
            $prompts = $aspect->prompts();
            $this->mandatoryAspects[$aspect->value] = array_fill(0, count($prompts), '');
        }
    }

    private function loadExistingData(): void
    {
        // First try to load existing agreement assessment
        $loaded = $this->loadAssessmentFromStage(GrantStage::Agreement, GrantStatus::CreatingAgreementAssessment);

        // If no agreement assessment exists and grant has proposal, pre-fill from planning
        if (! $loaded && $this->grant->ada_usulan) {
            $this->loadAssessmentFromStage(GrantStage::Planning, GrantStatus::CreatingPlanningAssessment);
        }
    }

    private function loadAssessmentFromStage(GrantStage $stage, GrantStatus $status): bool
    {
        $assessmentHistory = $this->grant->statusHistory()
            ->where('status_sesudah', $status)
            ->latest('id')
            ->first();

        if (! $assessmentHistory) {
            return false;
        }

        $existingAssessments = $assessmentHistory->assessments()
            ->where('tahapan', $stage)
            ->with('contents')
            ->get();

        if ($existingAssessments->isEmpty()) {
            return false;
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

        return true;
    }
}
