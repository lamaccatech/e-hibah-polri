<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">{{ __('page.chief-management.title') }}</flux:heading>

        <flux:button variant="primary" icon="plus" :href="route('chief.create')" wire:navigate>
            {{ __('page.chief-management.create-button') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('page.chief-management.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.chief-management.column-position') }}</flux:table.column>
            <flux:table.column>{{ __('page.chief-management.column-rank') }}</flux:table.column>
            <flux:table.column>{{ __('page.chief-management.column-nrp') }}</flux:table.column>
            <flux:table.column>{{ __('page.chief-management.column-status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.chief-management.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($chiefs as $chief)
                <flux:table.row :key="$chief->id">
                    <flux:table.cell>{{ $chief->nama_lengkap }}</flux:table.cell>
                    <flux:table.cell>{{ $chief->jabatan }}</flux:table.cell>
                    <flux:table.cell>{{ $chief->pangkat }}</flux:table.cell>
                    <flux:table.cell>{{ $chief->nrp }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($chief->sedang_menjabat)
                            <flux:badge variant="solid" color="green" size="sm">{{ __('page.chief-management.badge-active') }}</flux:badge>
                        @else
                            <flux:badge size="sm">{{ __('page.chief-management.badge-inactive') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            @unless ($chief->sedang_menjabat)
                                <flux:button variant="ghost" size="sm" icon="check-circle" wire:click="assign({{ $chief->id }})" />
                            @endunless
                            <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('chief.edit', $chief)" wire:navigate />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center">
                        {{ __('page.chief-management.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
