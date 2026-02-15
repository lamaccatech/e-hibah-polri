<?php

namespace App\Livewire\GrantPlanning;

use App\Enums\ProposalChapter;
use App\Models\Grant;
use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class ProposalDocument extends Component
{
    public Grant $grant;

    /** @var array<string, array<int, string>> */
    public array $chapters = [];

    /** @var array<int, array{uraian: string, volume: string, satuan: string, harga_satuan: string}> */
    public array $budgetItems = [];

    /** @var array<int, array{uraian_kegiatan: string, tanggal_mulai: string, tanggal_selesai: string}> */
    public array $schedules = [];

    public string $currency = 'IDR';

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);

        $this->grant = $grant;
        $this->initializeChapters();
        $this->loadExistingData();
    }

    protected function rules(): array
    {
        $rules = [
            'currency' => ['required', 'string', 'max:10'],
        ];

        // Validate chapters - at least the first paragraph of each chapter is required
        foreach ($this->planningChapters() as $chapter) {
            $rules["chapters.{$chapter->value}.0"] = ['required', 'string', 'min:10'];
        }

        // Budget items validation
        $rules['budgetItems'] = ['required', 'array', 'min:1'];
        $rules['budgetItems.*.uraian'] = ['required', 'string', 'max:500'];
        $rules['budgetItems.*.volume'] = ['required', 'numeric', 'min:0.01'];
        $rules['budgetItems.*.satuan'] = ['required', 'string', 'max:100'];
        $rules['budgetItems.*.harga_satuan'] = ['required', 'numeric', 'min:0'];

        // Schedule validation
        $rules['schedules'] = ['required', 'array', 'min:1'];
        $rules['schedules.*.uraian_kegiatan'] = ['required', 'string', 'max:500'];
        $rules['schedules.*.tanggal_mulai'] = ['required', 'date'];
        $rules['schedules.*.tanggal_selesai'] = ['required', 'date', 'after_or_equal:schedules.*.tanggal_mulai'];

        return $rules;
    }

    public function addBudgetItem(): void
    {
        $this->budgetItems[] = [
            'uraian' => '',
            'volume' => '',
            'satuan' => '',
            'harga_satuan' => '',
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

    public function save(GrantPlanningRepository $repository): void
    {
        $this->validate();

        $repository->saveProposalDocument(
            $this->grant,
            $this->chapters,
            $this->budgetItems,
            $this->schedules,
            $this->currency,
        );

        $this->redirect(route('grant-planning.assessment', $this->grant), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-planning.proposal-document', [
            'planningChapters' => $this->planningChapters(),
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
            $prompts = $chapter->prompts();
            $paragraphCount = max(count($prompts), 1);
            $this->chapters[$chapter->value] = array_fill(0, $paragraphCount, '');
        }

        $this->budgetItems = [[
            'uraian' => '',
            'volume' => '',
            'satuan' => '',
            'harga_satuan' => '',
        ]];

        $this->schedules = [[
            'uraian_kegiatan' => '',
            'tanggal_mulai' => '',
            'tanggal_selesai' => '',
        ]];
    }

    private function loadExistingData(): void
    {
        $existingInfo = $this->grant->information()
            ->where('tahapan', \App\Enums\GrantStage::Planning)
            ->with('contents')
            ->get();

        if ($existingInfo->isEmpty()) {
            return;
        }

        // Load chapters
        foreach ($existingInfo as $info) {
            $contents = $info->contents->sortBy('nomor_urut');
            $this->chapters[$info->judul] = $contents->pluck('isi')->values()->all();
        }

        // Load budget items
        $existingBudgets = $this->grant->budgetPlans;
        if ($existingBudgets->isNotEmpty()) {
            $this->budgetItems = $existingBudgets->map(fn ($b) => [
                'uraian' => $b->uraian,
                'volume' => $b->volume,
                'satuan' => $b->satuan,
                'harga_satuan' => $b->harga_satuan,
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
