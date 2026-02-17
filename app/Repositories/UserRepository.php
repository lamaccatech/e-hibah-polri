<?php

namespace App\Repositories;

use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /** @return LengthAwarePaginator<int, User> */
    public function paginateWithUnits(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return User::with('unit')
            ->whereHas('unit', function ($query) use ($search) {
                if ($search !== '') {
                    $query->where('nama_unit', 'ilike', "%{$search}%");
                }
            })
            ->paginate($perPage);
    }

    public function findWithUnit(int $id): User
    {
        return User::with('unit')->findOrFail($id);
    }

    public function create(array $userData, array $unitData): User
    {
        $user = User::create($userData);
        $user->unit()->create($unitData);

        return $user;
    }

    public function update(User $user, array $userData, array $unitData): void
    {
        $user->update($userData);
        $user->unit->update($unitData);
    }

    public function delete(User $user): void
    {
        if ($user->unit) {
            $user->unit->delete();
        }

        $user->delete();
    }

    public function unitHasGrants(OrgUnit $unit): bool
    {
        return $unit->grants()->exists();
    }

    public function unitHasChildren(OrgUnit $unit): bool
    {
        return $unit->children()->exists();
    }

    /** @return Collection<int, OrgUnit> */
    public function allUnits(): Collection
    {
        return OrgUnit::query()
            ->select(['id_user', 'nama_unit'])
            ->get();
    }
}
