<?php

namespace App\Repositories;

use App\Models\OrgUnit;
use App\Models\OrgUnitChief;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ChiefRepository
{
    /** @return Collection<int, OrgUnitChief> */
    public function allForUnit(OrgUnit $unit): Collection
    {
        return $unit->chiefs;
    }

    public function create(OrgUnit $unit, array $data): OrgUnitChief
    {
        return $unit->chiefs()->create($data);
    }

    public function update(OrgUnitChief $chief, array $data): void
    {
        $chief->update($data);
    }

    public function assignActive(OrgUnit $unit, int $chiefId): void
    {
        $chief = OrgUnitChief::where('id_unit', $unit->id_user)->findOrFail($chiefId);

        DB::transaction(function () use ($unit, $chief): void {
            OrgUnitChief::where('id_unit', $unit->id_user)
                ->where('sedang_menjabat', true)
                ->update(['sedang_menjabat' => false]);

            $chief->update(['sedang_menjabat' => true]);
        });
    }
}
