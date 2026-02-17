<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.donor-listing.title') }}</flux:heading>

    <div class="mb-4 w-1/4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('page.donor-listing.search-placeholder') }}" icon="magnifying-glass" clearable />
    </div>

    <flux:table :paginate="$donors">
        <flux:table.columns>
            <flux:table.column>{{ __('page.donor-listing.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.donor-listing.column-origin') }}</flux:table.column>
            <flux:table.column>{{ __('page.donor-listing.column-category') }}</flux:table.column>
            <flux:table.column>{{ __('page.donor-listing.column-country') }}</flux:table.column>
            <flux:table.column>{{ __('page.donor-listing.column-grant-count') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.donor-listing.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($donors as $donor)
                <flux:table.row :key="$donor->id">
                    <flux:table.cell>{{ $donor->nama }}</flux:table.cell>
                    <flux:table.cell>{{ $donor->asal }}</flux:table.cell>
                    <flux:table.cell>{{ $donor->kategori }}</flux:table.cell>
                    <flux:table.cell>{{ $donor->negara }}</flux:table.cell>
                    <flux:table.cell>{{ $donor->grants_count }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button variant="ghost" size="sm" icon="eye" :href="route('donor.show', $donor)" wire:navigate />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center">
                        {{ __('page.donor-listing.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
