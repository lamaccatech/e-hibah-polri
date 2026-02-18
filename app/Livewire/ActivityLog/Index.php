<?php

namespace App\Livewire\ActivityLog;

use App\Enums\LogAction;
use App\Models\ActivityLog;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $action = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($this->search, fn ($query, $search) => $query->whereHas('user', fn ($q) => $q->where('name', 'ilike', "%{$search}%")))
            ->when($this->action, fn ($query, $action) => $query->where('action', $action))
            ->when($this->dateFrom, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($this->dateTo, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('livewire.activity-log.index', [
            'logs' => $logs,
            'actionOptions' => LogAction::cases(),
        ]);
    }
}
