<?php

namespace App\Livewire\GrantPlanning;

use App\Enums\GrantStage;
use App\Enums\ProposalChapter;
use App\Models\Autocomplete;
use App\Models\Grant;
use App\Repositories\GrantPlanningRepository;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class ProposalDocument extends Component
{
    public Grant $grant;

    /** @var array<string, array<int, string>> */
    public array $chapters = [];

    /** @var array<int, array{purpose: string, detail: string}> */
    public array $objectives = [];

    /** @var array<int, array{uraian: string, nilai: string}> */
    public array $budgetItems = [];

    /** @var array<int, array{uraian_kegiatan: string, tanggal_mulai: string, tanggal_selesai: string}> */
    public array $schedules = [];

    public string $currency = 'IDR';

    /** @var array<int, array{title: string, paragraphs: array<int, string>}> */
    public array $customChapters = [];

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantPlanningRepository::class)->isEditable($grant), 403);

        $this->grant = $grant;
        $this->initializeChapters();
        $this->loadExistingData();
        $this->generatePurposeChapter();
    }

    protected function rules(): array
    {
        $rules = [
            'currency' => ['required', 'string', Rule::in(Autocomplete::where('identifier', 'mata_uang')->pluck('value'))],
        ];

        // Validate chapters - at least the first paragraph of each chapter is required
        // Skip Purpose (auto-generated), Objective (handled via $objectives), BudgetPlan (handled via $budgetItems)
        foreach ($this->planningChapters() as $chapter) {
            if (in_array($chapter, [ProposalChapter::Purpose, ProposalChapter::Objective, ProposalChapter::BudgetPlan])) {
                continue;
            }
            $rules["chapters.{$chapter->value}.0"] = ['required', 'string', 'min:10'];
        }

        // Objective validation
        $rules['objectives'] = ['required', 'array', 'min:1'];
        $rules['objectives.*.purpose'] = ['required', 'string'];
        $rules['objectives.*.detail'] = ['required', 'string', 'min:10'];

        // Budget items validation
        $rules['budgetItems'] = ['required', 'array', 'min:1'];
        $rules['budgetItems.*.uraian'] = ['required', 'string', 'max:500'];
        $rules['budgetItems.*.nilai'] = ['required', 'numeric', 'min:0'];

        // Schedule validation
        $rules['schedules'] = ['required', 'array', 'min:1'];
        $rules['schedules.*.uraian_kegiatan'] = ['required', 'string', 'max:500'];
        $rules['schedules.*.tanggal_mulai'] = ['required', 'date'];
        $rules['schedules.*.tanggal_selesai'] = ['required', 'date', 'after_or_equal:schedules.*.tanggal_mulai'];

        // Custom chapter validation
        foreach ($this->customChapters as $i => $chapter) {
            $rules["customChapters.{$i}.title"] = ['required', 'string', 'max:255'];
            foreach ($chapter['paragraphs'] as $j => $paragraph) {
                $rules["customChapters.{$i}.paragraphs.{$j}"] = ['required', 'string', 'min:10'];
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attributes = [
            'currency' => __('page.grant-planning-proposal.label-currency'),
            'objectives.*.purpose' => __('page.grant-planning-proposal.placeholder-purpose'),
            'objectives.*.detail' => __('page.grant-planning-proposal.add-objective'),
            'budgetItems.*.uraian' => __('page.grant-planning-proposal.label-description'),
            'budgetItems.*.nilai' => __('page.grant-planning-proposal.label-value'),
            'schedules.*.uraian_kegiatan' => __('page.grant-planning-proposal.label-activity'),
            'schedules.*.tanggal_mulai' => __('page.grant-planning-proposal.label-start-date'),
            'schedules.*.tanggal_selesai' => __('page.grant-planning-proposal.label-end-date'),
            'customChapters.*.title' => __('page.grant-planning-proposal.label-chapter-title'),
            'customChapters.*.paragraphs.*' => __('page.grant-planning-proposal.add-paragraph'),
        ];

        foreach ($this->planningChapters() as $chapter) {
            if (in_array($chapter, [ProposalChapter::Purpose, ProposalChapter::Objective, ProposalChapter::BudgetPlan])) {
                continue;
            }
            $attributes["chapters.{$chapter->value}.0"] = $chapter->label();
        }

        return $attributes;
    }

    public function addObjective(): void
    {
        $this->objectives[] = ['purpose' => '', 'detail' => ''];
    }

    public function removeObjective(int $index): void
    {
        unset($this->objectives[$index]);
        $this->objectives = array_values($this->objectives);
    }

    public function addBudgetItem(): void
    {
        $this->budgetItems[] = [
            'uraian' => '',
            'nilai' => '',
        ];
    }

    public function removeBudgetItem(int $index): void
    {
        unset($this->budgetItems[$index]);
        $this->budgetItems = array_values($this->budgetItems);
    }

    public function addSchedule(): void
    {
        $this->schedules[] = [
            'uraian_kegiatan' => '',
            'tanggal_mulai' => '',
            'tanggal_selesai' => '',
        ];
    }

    public function removeSchedule(int $index): void
    {
        unset($this->schedules[$index]);
        $this->schedules = array_values($this->schedules);
    }

    public function addCustomChapter(): void
    {
        $this->customChapters[] = [
            'title' => '',
            'paragraphs' => [''],
        ];
    }

    public function removeCustomChapter(int $index): void
    {
        unset($this->customChapters[$index]);
        $this->customChapters = array_values($this->customChapters);
    }

    public function addCustomChapterParagraph(int $chapterIndex): void
    {
        $this->customChapters[$chapterIndex]['paragraphs'][] = '';
    }

    public function removeCustomChapterParagraph(int $chapterIndex, int $paragraphIndex): void
    {
        unset($this->customChapters[$chapterIndex]['paragraphs'][$paragraphIndex]);
        $this->customChapters[$chapterIndex]['paragraphs'] = array_values($this->customChapters[$chapterIndex]['paragraphs']);
    }

    public function save(GrantPlanningRepository $repository): void
    {
        $this->generatePurposeChapter();
        $this->validate();

        // Convert objectives into structured chapter format with subjudul
        $this->chapters[ProposalChapter::Objective->value] = array_map(fn ($obj) => [
            'subjudul' => $obj['purpose'],
            'isi' => $obj['detail'],
        ], $this->objectives);

        // Transform regular chapters to include prompt text as subjudul
        foreach ($this->planningChapters() as $chapter) {
            if (in_array($chapter, [ProposalChapter::Purpose, ProposalChapter::Objective, ProposalChapter::BudgetPlan])) {
                continue;
            }

            $prompts = $chapter->prompts();
            $this->chapters[$chapter->value] = array_map(
                fn ($content, $i) => ['subjudul' => $prompts[$i] ?? '', 'isi' => $content],
                $this->chapters[$chapter->value],
                array_keys($this->chapters[$chapter->value]),
            );
        }

        // Filter out BudgetPlan from chapters (handled via budgetItems)
        $chaptersToSave = array_filter(
            $this->chapters,
            fn ($key) => $key !== ProposalChapter::BudgetPlan->value,
            ARRAY_FILTER_USE_KEY,
        );

        $repository->saveProposalDocument(
            $this->grant,
            $chaptersToSave,
            $this->budgetItems,
            $this->schedules,
            $this->currency,
            $this->customChapters,
        );

        $this->redirect(route('grant-planning.assessment', $this->grant), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.grant-planning.proposal-document', [
            'planningChapters' => $this->planningChapters(),
            'currencyOptions' => Autocomplete::where('identifier', 'mata_uang')->pluck('value'),
        ]);
    }

    /**
     * @return ProposalChapter[]
     */
    private function planningChapters(): array
    {
        return array_filter(
            ProposalChapter::cases(),
            fn (ProposalChapter $c) => ! in_array($c, [ProposalChapter::ReceptionBasis, ProposalChapter::SupervisionMechanism]),
        );
    }

    private function initializeChapters(): void
    {
        foreach ($this->planningChapters() as $chapter) {
            if (in_array($chapter, [ProposalChapter::Objective, ProposalChapter::BudgetPlan])) {
                continue;
            }

            $prompts = $chapter->prompts();
            $paragraphCount = max(count($prompts), 1);
            $this->chapters[$chapter->value] = array_fill(0, $paragraphCount, '');
        }

        $this->objectives = [['purpose' => '', 'detail' => '']];

        $this->budgetItems = [[
            'uraian' => '',
            'nilai' => '',
        ]];

        $this->schedules = [[
            'uraian_kegiatan' => '',
            'tanggal_mulai' => '',
            'tanggal_selesai' => '',
        ]];
    }

    private function generatePurposeChapter(): void
    {
        $donor = $this->grant->donor;

        if ($donor) {
            $this->chapters[ProposalChapter::Purpose->value] = [
                "<p>Maksud penyusunan Naskah ini adalah mengajukan permohonan pembiayaan kepada {$donor->nama} untuk kegiatan {$this->grant->nama_hibah}</p>",
            ];
        }
    }

    private function loadExistingData(): void
    {
        $existingInfo = $this->grant->information()
            ->where('tahapan', GrantStage::Planning)
            ->with('contents')
            ->get();

        if ($existingInfo->isEmpty()) {
            return;
        }

        // Load chapters
        foreach ($existingInfo as $info) {
            $contents = $info->contents->sortBy('nomor_urut');
            $chapter = ProposalChapter::tryFrom($info->judul);

            if ($chapter === ProposalChapter::Objective && $contents->isNotEmpty()) {
                $this->objectives = $contents->map(fn ($c) => [
                    'purpose' => $c->subjudul,
                    'detail' => $c->isi,
                ])->values()->all();
            } elseif ($chapter !== null) {
                $this->chapters[$info->judul] = $contents->pluck('isi')->values()->all();
            } else {
                // Custom chapter (not a known enum value)
                $this->customChapters[] = [
                    'title' => $info->judul,
                    'paragraphs' => $contents->pluck('isi')->values()->all(),
                ];
            }
        }

        // Load budget items
        $existingBudgets = $this->grant->budgetPlans()->orderBy('nomor_urut')->get();
        if ($existingBudgets->isNotEmpty()) {
            $this->budgetItems = $existingBudgets->map(fn ($b) => [
                'uraian' => $b->uraian,
                'nilai' => $b->nilai,
            ])->all();
        }

        // Load schedules
        $existingSchedules = $this->grant->activitySchedules;
        if ($existingSchedules->isNotEmpty()) {
            $this->schedules = $existingSchedules->map(fn ($s) => [
                'uraian_kegiatan' => $s->uraian_kegiatan,
                'tanggal_mulai' => $s->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $s->tanggal_selesai->format('Y-m-d'),
            ])->all();
        }

        // Load currency
        if ($this->grant->mata_uang) {
            $this->currency = $this->grant->mata_uang;
        }
    }
}
