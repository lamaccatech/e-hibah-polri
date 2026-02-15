<?php

namespace App\Livewire\UserManagement;

use App\Enums\UnitLevel;
use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Create extends Component
{
    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $unitName = '';

    public string $code = '';

    public string $unitLevel = '';

    public string $parentUnitId = '';

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'same:passwordConfirmation', Password::defaults()],
            'unitName' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'unitLevel' => ['required', Rule::enum(UnitLevel::class)],
            'parentUnitId' => ['required', 'exists:unit,id_user'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['unitName'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->unit()->create([
            'nama_unit' => $validated['unitName'],
            'kode' => $validated['code'],
            'level_unit' => $validated['unitLevel'],
            'id_unit_atasan' => $validated['parentUnitId'],
        ]);

        $this->redirect(route('user.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.user-management.create', [
            'unitLevels' => UnitLevel::options(),
            'parentUnits' => OrgUnit::query()
                ->select(['id_user', 'nama_unit'])
                ->get(),
        ]);
    }
}
