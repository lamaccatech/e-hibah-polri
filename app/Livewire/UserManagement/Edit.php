<?php

namespace App\Livewire\UserManagement;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $email = '';

    public string $unitName = '';

    public string $code = '';

    public function mount(User $user): void
    {
        $this->user = $user->load('unit');
        $this->email = $user->email;
        $this->unitName = $user->unit->nama_unit ?? '';
        $this->code = $user->unit->kode ?? '';
    }

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'unitName' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(UserRepository $repository): void
    {
        $validated = $this->validate();

        $repository->update(
            $this->user,
            ['email' => $validated['email']],
            [
                'nama_unit' => $validated['unitName'],
                'kode' => $validated['code'],
            ],
        );

        $this->redirect(route('user.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.user-management.edit');
    }
}
