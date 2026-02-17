<?php

namespace App\Livewire\DonorListing;

use App\Models\Donor;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Donor $donor;

    public function mount(Donor $donor): void
    {
        $this->donor = $donor->load(['grants.orgUnit', 'grants.statusHistory', 'tags']);
    }

    public function render(): View
    {
        return view('livewire.donor-listing.show');
    }
}
