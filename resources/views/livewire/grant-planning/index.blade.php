<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('page.grant-planning.title') }}</flux:heading>
    </div>

    @error('submit')
        <div class="mb-4">
            <flux:badge color="red">{{ $message }}</flux:badge>
        </div>
    @enderror

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('page.grant-planning.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-planning.column-donor') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-planning.column-value') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-planning.column-status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.grant-planning.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($grants as $grant)
                <flux:table.row :key="$grant->id">
                    <flux:table.cell>{{ $grant->nama_hibah }}</flux:table.cell>
                    <flux:table.cell>{{ $grant->donor?->nama ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($grant->nilai_hibah)
                            {{ $grant->mata_uang }} {{ number_format($grant->nilai_hibah, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm">
                            {{ $grant->statusHistory->last()?->status_sesudah?->value ?? '-' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('grant-planning.edit', $grant)" wire:navigate />
                            @if (in_array($grant->id, $submittableIds))
                                <flux:button variant="primary" size="sm" wire:click="submit({{ $grant->id }})">
                                    {{ __('page.grant-planning.submit-button') }}
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">
                        {{ __('page.grant-planning.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
