<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.activity-log.title') }}</flux:heading>

    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-64">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('page.activity-log.search-placeholder') }}" icon="magnifying-glass" clearable />
        </div>
        <div class="w-48">
            <flux:select wire:model.live="action" variant="listbox" placeholder="{{ __('page.activity-log.filter-action') }}">
                <flux:select.option value="">{{ __('page.activity-log.all-actions') }}</flux:select.option>
                @foreach ($actionOptions as $option)
                    <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div class="w-44">
            <flux:date-picker wire:model.live="dateFrom" :label="__('page.activity-log.date-from')" locale="id-ID" />
        </div>
        <div class="w-44">
            <flux:date-picker wire:model.live="dateTo" :label="__('page.activity-log.date-to')" locale="id-ID" />
        </div>
    </div>

    <flux:table :paginate="$logs">
        <flux:table.columns>
            <flux:table.column>{{ __('page.activity-log.column-time') }}</flux:table.column>
            <flux:table.column>{{ __('page.activity-log.column-user') }}</flux:table.column>
            <flux:table.column>{{ __('page.activity-log.column-action') }}</flux:table.column>
            <flux:table.column>{{ __('page.activity-log.column-message') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($logs as $log)
                <flux:table.row :key="$log->id">
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $log->user?->name ?? 'Sistem' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $variant = match ($log->action) {
                                \App\Enums\LogAction::Create => 'success',
                                \App\Enums\LogAction::Update => 'info',
                                \App\Enums\LogAction::Delete => 'danger',
                                \App\Enums\LogAction::Submit => 'info',
                                \App\Enums\LogAction::Review => 'warning',
                                \App\Enums\LogAction::Verify => 'success',
                                \App\Enums\LogAction::Reject => 'danger',
                                \App\Enums\LogAction::RequestRevision => 'warning',
                                default => 'default',
                            };
                        @endphp
                        <flux:badge size="sm" :variant="$variant">{{ $log->action->label() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $log->message }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center">
                        {{ __('page.activity-log.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
