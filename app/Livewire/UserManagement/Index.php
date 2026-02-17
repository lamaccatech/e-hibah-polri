<?php

namespace App\Livewire\UserManagement;

use App\Repositories\UserRepository;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showDeleteModal = false;

    public ?int $userToDelete = null;

    public function confirmDelete(int $userId): void
    {
        $this->userToDelete = $userId;
        $this->showDeleteModal = true;
    }

    public function delete(UserRepository $repository): void
    {
        $user = $repository->findWithUnit($this->userToDelete);
        $unit = $user->unit;

        if ($unit && $repository->unitHasGrants($unit)) {
            $this->addError('delete', __('page.user-management.error-has-grants'));
            $this->showDeleteModal = false;
            $this->userToDelete = null;

            return;
        }

        if ($unit && $repository->unitHasChildren($unit)) {
            $this->addError('delete', __('page.user-management.error-has-subordinates'));
            $this->showDeleteModal = false;
            $this->userToDelete = null;

            return;
        }

        $repository->delete($user);

        $this->showDeleteModal = false;
        $this->userToDelete = null;

        $this->redirect(route('user.index'), navigate: true);
    }

    public function render(UserRepository $repository)
    {
        return view('livewire.user-management.index', [
            'users' => $repository->paginateWithUnits(),
        ]);
    }
}
