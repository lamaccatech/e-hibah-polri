<?php

namespace App\Livewire\GrantAgreement;

use App\Enums\GrantStage;
use App\Enums\ProposalChapter;
use App\Models\Autocomplete;
use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Harmonization extends Component
{
    public Grant $grant;

    /** @var list<string> */
    public array $grantForms = [];

    public string $currency = '';

    /** @var array<int, array{uraian: string, nilai: string}> */
    public array $budgetItems = [
        ['uraian' => '', 'nilai' => ''],
    ];

    /** @var array<int, array{uraian: string, tanggal: string, nilai: string}> */
    public array $withdrawalPlans = [
        ['uraian' => '', 'tanggal' => '', 'nilai' => ''],
    ];

    /** @var array<int, string> */
    public array $supervisionParagraphs = [''];

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantAgreementRepository::class)->isEditable($grant), 403);

        $this->grant = $grant;
        $this->loadExistingData();
    }

    protected function rules(): array
    {
        $rules = [
            'grantForms' => ['required', 'array', 'min:1'],
            'grantForms.*' => ['required', 'string', Rule::in(['UANG', 'BARANG', 'JASA'])],
            'currency' => ['required', 'string', Rule::in(Autocomplete::where('identifier', 'mata_uang')->pluck('value'))],
            'budgetItems' => ['required', 'array', 'min:1'],
            'budgetItems.*.uraian' => ['required', 'string'],
            'budgetItems.*.nilai' => ['required', 'numeric', 'min:0'],
            'withdrawalPlans' => ['required', 'array', 'min:1'],
            'withdrawalPlans.*.uraian' => ['required', 'string'],
            'withdrawalPlans.*.tanggal' => ['required', 'date'],
            'withdrawalPlans.*.nilai' => ['required', 'numeric', 'min:0'],
            'supervisionParagraphs' => ['required', 'array', 'min:1'],
            'supervisionParagraphs.*' => ['required', 'string', 'min:10'],
        ];

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'grantForms' => __('page.grant-agreement-harmonization.label-grant-forms'),
            'currency' => __('page.grant-planning-proposal.label-currency'),
            'budgetItems.*.uraian' => __('page.grant-planning-proposal.label-description'),
            'budgetItems.*.nilai' => __('page.grant-planning-proposal.label-value'),
            'withdrawalPlans.*.uraian' => __('page.grant-planning-proposal.label-description'),
            'withdrawalPlans.*.tanggal' => __('page.grant-agreement-harmonization.label-withdrawal-date'),
            'withdrawalPlans.*.nilai' => __('page.grant-planning-proposal.label-value'),
            'supervisionParagraphs.*' => __('page.grant-agreement-harmonization.label-supervision'),
        ];
    }

    public function addBudgetItem(): void
    {
        $this->budgetItems[] = ['uraian' => '', 'nilai' => ''];
    }

    public function removeBudgetItem(int $index): void
    {
        if (count($this->budgetItems) > 1) {
            unset($this->budgetItems[$index]);
            $this->budgetItems = array_values($this->budgetItems);
        }
    }

    public function addWithdrawalPlan(): void
    {
        $this->withdrawalPlans[] = ['uraian' => '', 'tanggal' => '', 'nilai' => ''];
    }

    public function removeWithdrawalPlan(int $index): void
    {
        if (count($this->withdrawalPlans) > 1) {
            unset($this->withdrawalPlans[$index]);
            $this->withdrawalPlans = array_values($this->withdrawalPlans);
        }
    }

    public function addSupervisionParagraph(): void
    {
        $this->supervisionParagraphs[] = '';
    }

    public function removeSupervisionParagraph(int $index): void
    {
        if (count($this->supervisionParagraphs) > 1) {
            unset($this->supervisionParagraphs[$index]);
            $this->supervisionParagraphs = array_values($this->supervisionParagraphs);
        }
    }

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        // Validate withdrawal total does not exceed budget total
        $budgetTotal = collect($this->budgetItems)->sum(fn ($item) => (float) $item['nilai']);
        $withdrawalTotal = collect($this->withdrawalPlans)->sum(fn ($plan) => (float) $plan['nilai']);

        if ($withdrawalTotal > $budgetTotal) {
            $this->addError('withdrawalPlans', __('page.grant-agreement-harmonization.error-withdrawal-exceeds-budget'));

            return;
        }

        $repository->saveHarmonization(
            $this->grant,
            $this->grantForms,
            $this->currency,
            $this->budgetItems,
            $this->withdrawalPlans,
            $this->supervisionParagraphs,
        );

        $this->redirect(route('grant-agreement.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-agreement.harmonization', [
            'grantFormOptions' => ['UANG', 'BARANG', 'JASA'],
            'currencyOptions' => Autocomplete::where('identifier', 'mata_uang')->pluck('value'),
        ]);
    }

    private function loadExistingData(): void
    {
        // Load grant forms (stored as CSV string)
        if ($this->grant->bentuk_hibah) {
            $this->grantForms = explode(',', $this->grant->bentuk_hibah);
        }

        // Load currency
        $this->currency = $this->grant->mata_uang ?? '';

        // Load budget items (from agreement or pre-fill from planning)
        $this->loadBudgetItems();

        // Load withdrawal plans
        $this->loadWithdrawalPlans();

        // Load supervision paragraphs
        $this->loadSupervisionParagraphs();
    }

    private function loadBudgetItems(): void
    {
        $items = $this->grant->budgetPlans()
            ->orderBy('nomor_urut')
            ->get();

        if ($items->isNotEmpty()) {
            $this->budgetItems = $items->map(fn ($item) => [
                'uraian' => $item->uraian,
                'nilai' => $item->nilai,
            ])->all();

            // Also load currency from grant if available
            if ($this->grant->mata_uang) {
                $this->currency = $this->grant->mata_uang;
            }
        }
    }

    private function loadWithdrawalPlans(): void
    {
        $plans = $this->grant->withdrawalPlans()
            ->orderBy('nomor_urut')
            ->get();

        if ($plans->isNotEmpty()) {
            $this->withdrawalPlans = $plans->map(fn ($plan) => [
                'uraian' => $plan->uraian,
                'tanggal' => $plan->tanggal->format('Y-m-d'),
                'nilai' => $plan->nilai,
            ])->all();
        }
    }

    private function loadSupervisionParagraphs(): void
    {
        $info = $this->grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->where('judul', ProposalChapter::SupervisionMechanism->value)
            ->with('contents')
            ->first();

        if ($info && $info->contents->isNotEmpty()) {
            $this->supervisionParagraphs = $info->contents
                ->sortBy('nomor_urut')
                ->pluck('isi')
                ->values()
                ->all();
        }
    }
}
