<?php

namespace App\Livewire\GrantAgreement;

use App\Models\Autocomplete;
use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Livewire\Component;

class SehatiSubmission extends Component
{
    public Grant $grant;

    public string $grantRecipient = '';

    public string $fundingSource = '';

    public string $fundingType = '';

    public string $withdrawalMethod = '';

    public string $effectiveDate = '';

    public string $withdrawalDeadline = '';

    public string $accountClosingDate = '';

    public string $grantRecipientSearch = '';

    public string $fundingSourceSearch = '';

    public string $fundingTypeSearch = '';

    public string $withdrawalMethodSearch = '';

    /** @var array<string, array{search: string, identifier: string}> */
    private const AUTOCOMPLETE_FIELDS = [
        'grantRecipient' => ['search' => 'grantRecipientSearch', 'identifier' => 'penerima_hibah'],
        'fundingSource' => ['search' => 'fundingSourceSearch', 'identifier' => 'sumber_pembiayaan'],
        'fundingType' => ['search' => 'fundingTypeSearch', 'identifier' => 'jenis_pembiayaan'],
        'withdrawalMethod' => ['search' => 'withdrawalMethodSearch', 'identifier' => 'cara_penarikan'],
    ];

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantAgreementRepository::class)->canSubmitSehati($grant), 403);

        $this->grant = $grant;

        // Pre-fill from existing submission if revisiting
        if ($submission = $grant->financeMinistrySubmission) {
            $this->grantRecipient = $submission->penerima_hibah ?? '';
            $this->fundingSource = $submission->sumber_pembiayaan ?? '';
            $this->fundingType = $submission->jenis_pembiayaan ?? '';
            $this->withdrawalMethod = $submission->cara_penarikan ?? '';
            $this->effectiveDate = $submission->tanggal_efektif?->format('Y-m-d') ?? '';
            $this->withdrawalDeadline = $submission->tanggal_batas_penarikan?->format('Y-m-d') ?? '';
            $this->accountClosingDate = $submission->tanggal_penutupan_rekening?->format('Y-m-d') ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'grantRecipient' => ['required', 'string'],
            'fundingSource' => ['required', 'string'],
            'fundingType' => ['required', 'string'],
            'withdrawalMethod' => ['required', 'string'],
            'effectiveDate' => ['required', 'date'],
            'withdrawalDeadline' => ['required', 'date', 'after:effectiveDate'],
            'accountClosingDate' => ['required', 'date', 'after:withdrawalDeadline'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'grantRecipient' => __('page.grant-agreement-sehati.label-grant-recipient'),
            'fundingSource' => __('page.grant-agreement-sehati.label-funding-source'),
            'fundingType' => __('page.grant-agreement-sehati.label-funding-type'),
            'withdrawalMethod' => __('page.grant-agreement-sehati.label-withdrawal-method'),
            'effectiveDate' => __('page.grant-agreement-sehati.label-effective-date'),
            'withdrawalDeadline' => __('page.grant-agreement-sehati.label-withdrawal-deadline'),
            'accountClosingDate' => __('page.grant-agreement-sehati.label-account-closing-date'),
        ];
    }

    public function createOption(string $field): void
    {
        $config = self::AUTOCOMPLETE_FIELDS[$field] ?? null;

        if (! $config) {
            return;
        }

        $value = trim($this->{$config['search']});

        if ($value === '') {
            return;
        }

        Autocomplete::firstOrCreate([
            'identifier' => $config['identifier'],
            'value' => $value,
        ]);

        $this->{$field} = $value;
        $this->{$config['search']} = '';
    }

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        $repository->saveSehatiSubmission($this->grant, [
            'penerima_hibah' => $this->grantRecipient,
            'sumber_pembiayaan' => $this->fundingSource,
            'jenis_pembiayaan' => $this->fundingType,
            'cara_penarikan' => $this->withdrawalMethod,
            'tanggal_efektif' => $this->effectiveDate,
            'tanggal_batas_penarikan' => $this->withdrawalDeadline,
            'tanggal_penutupan_rekening' => $this->accountClosingDate,
        ]);

        $this->redirect(route('grant-detail.show', $this->grant), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-agreement.sehati-submission', [
            'grantRecipientOptions' => Autocomplete::where('identifier', 'penerima_hibah')->pluck('value'),
            'fundingSourceOptions' => Autocomplete::where('identifier', 'sumber_pembiayaan')->pluck('value'),
            'fundingTypeOptions' => Autocomplete::where('identifier', 'jenis_pembiayaan')->pluck('value'),
            'withdrawalMethodOptions' => Autocomplete::where('identifier', 'cara_penarikan')->pluck('value'),
        ]);
    }
}
