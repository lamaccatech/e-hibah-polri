<?php

namespace App\Livewire\GrantAgreement;

use App\Enums\FileType;
use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReceptionBasis extends Component
{
    use WithFileUploads;

    public ?Grant $grant = null;

    public string $activityName = '';

    public string $letterNumber = '';

    public bool $hasProposal = false;

    /** @var array<int, array{purpose: string, detail: string}> */
    public array $objectives = [
        ['purpose' => '', 'detail' => ''],
    ];

    public $donorLetter;

    private ?Grant $matchedPlanningGrant = null;

    public function mount(?Grant $grant = null): void
    {
        if ($grant?->exists) {
            $userUnit = auth()->user()->unit;
            abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
            abort_unless(app(GrantAgreementRepository::class)->isEditable($grant), 403);

            $this->grant = $grant;
            $this->activityName = str($grant->nama_hibah)->upper()->toString();
            $this->letterNumber = $grant->nomor_surat_dari_calon_pemberi_hibah ?? '';
            $this->hasProposal = $grant->ada_usulan;

            $this->loadExistingObjectives();
        }
    }

    public function updatedLetterNumber(): void
    {
        $this->lookupPlanningNumber();
    }

    public function addObjective(): void
    {
        $this->objectives[] = ['purpose' => '', 'detail' => ''];
    }

    public function removeObjective(int $index): void
    {
        if (count($this->objectives) > 1) {
            unset($this->objectives[$index]);
            $this->objectives = array_values($this->objectives);
        }
    }

    protected function rules(): array
    {
        $rules = [
            'activityName' => ['required', 'string', 'max:255'],
            'letterNumber' => ['required', 'string', 'max:255'],
            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*.purpose' => ['required', 'string'],
            'objectives.*.detail' => ['required', 'string', 'min:10'],
        ];

        if (! $this->hasProposal && ! $this->grant) {
            $rules['donorLetter'] = ['required', 'file', 'mimes:pdf,jpg,png', 'max:10240'];
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'activityName' => __('page.grant-agreement-reception.label-activity-name'),
            'letterNumber' => __('page.grant-agreement-reception.label-letter-number'),
            'donorLetter' => __('page.grant-agreement-reception.label-donor-letter'),
            'objectives.*.purpose' => __('page.grant-planning-proposal.placeholder-purpose'),
            'objectives.*.detail' => __('page.grant-agreement-reception.label-objective-detail'),
        ];
    }

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        $activityName = str($this->activityName)->upper()->toString();
        $objectives = $this->objectives;

        if ($this->grant) {
            // Edit existing agreement
            $repository->updateReceptionBasis($this->grant, $activityName, $this->letterNumber, $objectives);
            $grant = $this->grant;
        } else {
            // Check for planning grant match
            $this->lookupPlanningNumber();
            $planningGrant = $this->matchedPlanningGrant;

            if ($planningGrant) {
                // Transition from planning
                $planningGrant->update([
                    'nama_hibah' => $activityName,
                    'nomor_surat_dari_calon_pemberi_hibah' => $this->letterNumber,
                ]);
                $repository->transitionFromPlanning($planningGrant, $objectives);
                $grant = $planningGrant;
            } else {
                // New direct agreement
                $grant = $repository->createAgreement(
                    auth()->user()->unit,
                    $activityName,
                    $this->letterNumber,
                    $objectives
                );

                // Attach donor letter to status history
                if ($this->donorLetter) {
                    $statusHistory = $grant->statusHistory()->latest('id')->first();
                    $statusHistory?->attachFile($this->donorLetter, FileType::DonorLetter);
                }
            }
        }

        $this->redirect(route('grant-agreement.donor', $grant), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.grant-agreement.reception-basis', [
            'purposeOptions' => config('options.grant_purposes'),
        ]);
    }

    private function lookupPlanningNumber(): void
    {
        if (empty($this->letterNumber)) {
            $this->hasProposal = false;
            $this->matchedPlanningGrant = null;

            return;
        }

        // Don't re-lookup if editing an existing agreement
        if ($this->grant) {
            return;
        }

        $repository = app(GrantAgreementRepository::class);
        $planningGrant = $repository->findPlanningGrantByNumber($this->letterNumber, auth()->user()->unit);

        if ($planningGrant) {
            $this->hasProposal = true;
            $this->matchedPlanningGrant = $planningGrant;
            $this->activityName = str($planningGrant->nama_hibah)->upper()->toString();
            $this->loadPlanningObjectives($planningGrant);
        } else {
            $this->hasProposal = false;
            $this->matchedPlanningGrant = null;
        }
    }

    private function loadExistingObjectives(): void
    {
        if (! $this->grant) {
            return;
        }

        $info = $this->grant->information()
            ->where('tahapan', \App\Enums\GrantStage::Agreement)
            ->where('judul', \App\Enums\ProposalChapter::Objective->value)
            ->with('contents')
            ->first();

        if ($info && $info->contents->isNotEmpty()) {
            $this->objectives = $info->contents
                ->sortBy('nomor_urut')
                ->map(fn ($c) => ['purpose' => $c->subjudul ?? '', 'detail' => $c->isi ?? ''])
                ->values()
                ->all();
        }
    }

    private function loadPlanningObjectives(Grant $planningGrant): void
    {
        $info = $planningGrant->information()
            ->where('tahapan', \App\Enums\GrantStage::Planning)
            ->where('judul', \App\Enums\ProposalChapter::Objective->value)
            ->with('contents')
            ->first();

        if ($info && $info->contents->isNotEmpty()) {
            $this->objectives = $info->contents
                ->sortBy('nomor_urut')
                ->map(fn ($c) => ['purpose' => $c->subjudul ?? '', 'detail' => $c->isi ?? ''])
                ->values()
                ->all();
        }
    }
}
