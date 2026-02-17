<?php

namespace App\Livewire\DonorListing;

use App\Models\Donor;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $donors = Donor::query()
            ->withCount('grants')
            ->when($this->search, fn ($query, $search) => $query->where('nama', 'ilike', "%{$search}%"))
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.donor-listing.index', [
            'donors' => $donors,
        ]);
    }
}
