<?php

namespace App\Livewire\ChiefManagement;

use App\Models\OrgUnitChief;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public function assign(int $id): void
    {
        $unit = auth()->user()->unit;
        $chief = OrgUnitChief::where('id_unit', $unit->id_user)->findOrFail($id);

        DB::transaction(function () use ($unit, $chief): void {
            OrgUnitChief::where('id_unit', $unit->id_user)
                ->where('sedang_menjabat', true)
                ->update(['sedang_menjabat' => false]);

            $chief->update(['sedang_menjabat' => true]);
        });
    }

    public function render()
    {
        return view('livewire.chief-management.index', [
            'chiefs' => auth()->user()->unit->chiefs,
        ]);
    }
}
