<?php

namespace App\Livewire\ChiefManagement;

use App\Models\OrgUnitChief;
use App\Repositories\ChiefRepository;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public OrgUnitChief $chief;

    public string $fullName = '';

    public string $position = '';

    public string $rank = '';

    public string $nrp = '';

    public $signature;

    public function mount(OrgUnitChief $chief): void
    {
        if ($chief->id_unit !== auth()->user()->unit->id_user) {
            abort(403);
        }

        $this->chief = $chief;
        $this->fullName = $chief->nama_lengkap;
        $this->position = $chief->jabatan;
        $this->rank = $chief->pangkat;
        $this->nrp = $chief->nrp;
    }

    protected function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'rank' => ['required', 'string', 'max:255'],
            'nrp' => ['required', 'string', 'max:255'],
            'signature' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(ChiefRepository $repository): void
    {
        $validated = $this->validate();

        $data = [
            'nama_lengkap' => $validated['fullName'],
            'jabatan' => $validated['position'],
            'pangkat' => $validated['rank'],
            'nrp' => $validated['nrp'],
        ];

        if ($this->signature) {
            $data['tanda_tangan'] = $this->signature->store('signatures');
        }

        $repository->update($this->chief, $data);

        $this->redirect(route('chief.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.chief-management.edit');
    }
}
