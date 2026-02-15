<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">{{ __('User Management') }}</flux:heading>

        <flux:button variant="primary" icon="plus" :href="route('user.create')" wire:navigate>
            {{ __('Tambah User') }}
        </flux:button>
    </div>

    @error('delete')
        <flux:callout variant="danger" class="mb-4">
            {{ $message }}
        </flux:callout>
    @enderror

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Nama Unit') }}</flux:table.column>
            <flux:table.column>{{ __('Kode') }}</flux:table.column>
            <flux:table.column>{{ __('Level') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Aksi') }}</flux:table.column>
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
                        {{ __('Belum ada user.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="showDeleteModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus User') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Hapus user dan unit ini? Tindakan ini tidak dapat dibatalkan.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="delete">
                    {{ __('Hapus') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
