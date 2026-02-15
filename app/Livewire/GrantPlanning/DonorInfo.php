<?php

namespace App\Livewire\GrantPlanning;

use App\Models\Grant;
use App\Repositories\GrantPlanningRepository;
use Livewire\Component;

class DonorInfo extends Component
{
    public Grant $grant;

    public string $donorMode = 'existing';

    public ?int $selectedDonorId = null;

    public string $name = '';

    public string $origin = '';

    public string $address = '';

    public string $country = '';

    public string $category = '';

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);

        $this->grant = $grant;

        if ($grant->id_pemberi_hibah) {
            $this->donorMode = 'existing';
            $this->selectedDonorId = $grant->id_pemberi_hibah;
        }
    }

    protected function rules(): array
    {
        if ($this->donorMode === 'existing') {
            return [
                'selectedDonorId' => ['required', 'exists:pemberi_hibah,id'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'origin' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'country' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(GrantPlanningRepository $repository): void
    {
        $validated = $this->validate();

        if ($this->donorMode === 'new') {
            $donor = $repository->createDonor([
                'nama' => $validated['name'],
                'asal' => $validated['origin'],
                'alamat' => $validated['address'],
                'negara' => $validated['country'],
                'kategori' => $validated['category'],
            ]);
            $donorId = $donor->id;
        } else {
            $donorId = $validated['selectedDonorId'];
        }

        $repository->linkDonor($this->grant, $donorId);

        $this->redirect(route('grant-planning.proposal-document', $this->grant), navigate: true);
    }

    public function render(GrantPlanningRepository $repository)
    {
        return view('livewire.grant-planning.donor-info', [
            'donors' => $repository->allDonors(),
        ]);
    }
}
