<?php

namespace App\Livewire\GrantPlanning;

use App\Enums\AssessmentAspect;
use App\Enums\GrantStage;
use App\Models\Grant;
use App\Repositories\GrantPlanningRepository;
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

        // Mandatory aspects
        foreach (AssessmentAspect::cases() as $aspect) {
            $aspects[] = [
                'judul' => $aspect->label(),
                'aspek' => $aspect->value,
                'paragraphs' => $this->mandatoryAspects[$aspect->value],
            ];
        }

        // Custom aspects
        foreach ($this->customAspects as $custom) {
            $aspects[] = [
                'judul' => $custom['title'],
                'aspek' => null,
                'paragraphs' => $custom['paragraphs'],
            ];
        }

        $repository->saveAssessment($this->grant, $aspects);

        $this->redirect(route('grant-planning.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-planning.assessment', [
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
        $existingAssessments = $this->grant->statusHistory()
            ->with(['assessments' => fn ($q) => $q->where('tahapan', GrantStage::Planning)->with('contents')])
            ->get()
            ->pluck('assessments')
            ->flatten();

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
