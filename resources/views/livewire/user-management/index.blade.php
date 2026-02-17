<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">{{ __('page.user-management.title') }}</flux:heading>

        <flux:button variant="primary" icon="plus" :href="route('user.create')" wire:navigate>
            {{ __('page.user-management.create-button') }}
        </flux:button>
    </div>

    <div class="mb-4 w-1/4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('page.user-management.search-placeholder') }}" icon="magnifying-glass" clearable />
    </div>

    @error('delete')
        <flux:callout variant="danger" class="mb-4">
            {{ $message }}
        </flux:callout>
    @enderror

    <flux:table :paginate="$users">
        <flux:table.columns>
            <flux:table.column>{{ __('common.email') }}</flux:table.column>
            <flux:table.column>{{ __('page.user-management.column-unit-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.user-management.column-code') }}</flux:table.column>
            <flux:table.column>{{ __('page.user-management.column-level') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.user-management.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>{{ $user->unit?->nama_unit }}</flux:table.cell>
                    <flux:table.cell>{{ $user->unit?->kode }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm">{{ $user->unit?->level_unit?->label() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('user.edit', $user)" wire:navigate />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="confirmDelete({{ $user->id }})" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">
                        {{ __('page.user-management.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="showDeleteModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('page.user-management.delete-modal-title') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('page.user-management.delete-modal-description') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="delete">
                    {{ __('common.delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
