<div>
    <div class="mb-6 flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('donor.index')" wire:navigate />
        <flux:heading size="xl">{{ __('page.donor-detail.title') }}</flux:heading>
    </div>

    {{-- Section 1: Donor Information --}}
    <div class="mb-8 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:heading size="lg" class="mb-4">{{ __('page.donor-detail.section-donor-info') }}</flux:heading>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-name') }}</dt>
                <dd class="mt-1">{{ $donor->nama }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-origin') }}</dt>
                <dd class="mt-1">{{ $donor->asal ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-address') }}</dt>
                <dd class="mt-1">{{ $donor->alamat ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-country') }}</dt>
                <dd class="mt-1">{{ $donor->negara ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-province') }}</dt>
                <dd class="mt-1">{{ $donor->nama_provinsi ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-regency') }}</dt>
                <dd class="mt-1">{{ $donor->nama_kabupaten_kota ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-phone') }}</dt>
                <dd class="mt-1">{{ $donor->nomor_telepon ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-email') }}</dt>
                <dd class="mt-1">{{ $donor->email ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('page.donor-detail.label-category') }}</dt>
                <dd class="mt-1">{{ $donor->kategori ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Section 2: Linked Grants --}}
    <div class="mb-8">
        <flux:heading size="lg" class="mb-4">{{ __('page.donor-detail.section-grants') }}</flux:heading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('page.donor-detail.column-grant-name') }}</flux:table.column>
                <flux:table.column>{{ __('page.donor-detail.column-value') }}</flux:table.column>
                <flux:table.column>{{ __('page.donor-detail.column-satker') }}</flux:table.column>
                <flux:table.column>{{ __('page.donor-detail.column-stage') }}</flux:table.column>
                <flux:table.column>{{ __('page.donor-detail.column-status') }}</flux:table.column>
                <flux:table.column align="end">{{ __('page.donor-detail.column-action') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($donor->grants as $grant)
                    <flux:table.row :key="$grant->id">
                        <flux:table.cell>{{ $grant->nama_hibah }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($grant->nilai_hibah)
                                {{ $grant->mata_uang }} {{ number_format($grant->nilai_hibah, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $grant->orgUnit?->nama_unit }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $grant->tahapan->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm">{{ $grant->statusHistory->last()?->status_sesudah?->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="ghost" size="sm" icon="eye" :href="route('grant-detail.show', $grant)" wire:navigate />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center">
                            {{ __('page.donor-detail.empty-grants') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

</div>
