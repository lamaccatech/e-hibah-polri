<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('page.grant-review.title') }}</flux:heading>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('page.grant-review.column-unit') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-donor') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-value') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-status') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($grants as $grant)
                <flux:table.row :key="$grant->id">
                    <flux:table.cell>{{ $grant->orgUnit?->nama_unit ?? '-' }}</flux:table.cell>
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
                            {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                        </flux:badge>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">
                        {{ __('page.grant-review.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
