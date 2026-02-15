<?php

namespace App\Livewire\ChiefManagement;

use App\Repositories\ChiefRepository;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $fullName = '';

    public string $position = '';

    public string $rank = '';

    public string $nrp = '';

    public $signature;

    protected function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'rank' => ['required', 'string', 'max:255'],
            'nrp' => ['required', 'string', 'max:255'],
            'signature' => ['required', 'image', 'max:2048'],
        ];
    }

    public function save(ChiefRepository $repository): void
    {
        $validated = $this->validate();

        $path = $this->signature->store('signatures');

        $repository->create(auth()->user()->unit, [
            'nama_lengkap' => $validated['fullName'],
            'jabatan' => $validated['position'],
            'pangkat' => $validated['rank'],
            'nrp' => $validated['nrp'],
            'tanda_tangan' => $path,
        ]);

        $this->redirect(route('chief.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.chief-management.create');
    }
}
