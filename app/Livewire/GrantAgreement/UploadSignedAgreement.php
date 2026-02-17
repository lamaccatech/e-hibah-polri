<?php

namespace App\Livewire\GrantAgreement;

use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadSignedAgreement extends Component
{
    use WithFileUploads;

    public Grant $grant;

    public $signedAgreementFile;

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantAgreementRepository::class)->canUploadSignedAgreement($grant), 403);

        $this->grant = $grant;
    }

    protected function rules(): array
    {
        return [
            'signedAgreementFile' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'signedAgreementFile' => __('page.grant-agreement-upload-signed.label-file'),
        ];
    }

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        $repository->saveSignedAgreement($this->grant, $this->signedAgreementFile);

        $this->redirect(route('grant-agreement.sehati', $this->grant), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.grant-agreement.upload-signed-agreement');
    }
}
