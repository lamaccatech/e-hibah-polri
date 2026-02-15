<?php

namespace App\Livewire\UserManagement;

use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public bool $showDeleteModal = false;

    public ?int $userToDelete = null;

    public function confirmDelete(int $userId): void
    {
        $this->userToDelete = $userId;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $user = User::with('unit')->findOrFail($this->userToDelete);
        $unit = $user->unit;

        if ($unit && $unit->grants()->exists()) {
            $this->addError('delete', __('page.user-management.error-has-grants'));
            $this->showDeleteModal = false;
            $this->userToDelete = null;

            return;
        }

        if ($unit && $unit->children()->exists()) {
            $this->addError('delete', __('page.user-management.error-has-subordinates'));
            $this->showDeleteModal = false;
            $this->userToDelete = null;

            return;
        }

        if ($unit) {
            $unit->delete();
        }

        $user->delete();

        $this->showDeleteModal = false;
        $this->userToDelete = null;

        $this->redirect(route('user.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.user-management.index', [
            'users' => User::with('unit')->whereHas('unit')->get(),
        ]);
    }
}
