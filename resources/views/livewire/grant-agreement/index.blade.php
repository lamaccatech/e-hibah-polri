<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('page.grant-agreement.title') }}</flux:heading>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('page.grant-agreement.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-agreement.column-donor') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-agreement.column-status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.grant-agreement.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($grants as $grant)
                <flux:table.row :key="$grant->id">
                    <flux:table.cell>{{ $grant->nama_hibah }}</flux:table.cell>
                    <flux:table.cell>{{ $grant->donor?->nama ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm">
                            {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('grant-agreement.reception-basis', $grant)" wire:navigate />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center">
                        {{ __('page.grant-agreement.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
