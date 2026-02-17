<?php

namespace App\Livewire\GrantAgreement;

use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Livewire\Component;
use Livewire\WithFileUploads;

class DraftAgreement extends Component
{
    use WithFileUploads;

    public Grant $grant;

    public $draftFile;

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantAgreementRepository::class)->isEditable($grant), 403);

        $this->grant = $grant;
    }

    protected function rules(): array
    {
        return [
            'draftFile' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'draftFile' => __('page.grant-agreement-draft.label-file'),
        ];
    }

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        $repository->saveDraftAgreement($this->grant, $this->draftFile);

        $this->redirect(route('grant-agreement.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-agreement.draft-agreement');
    }
}
