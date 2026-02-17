<x-grant-agreement.step-layout :grant="$grant" :currentStep="2">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-donor.title') }}</flux:heading>

    @if ($isReadOnly)
        {{-- Read-only display for grants with proposals --}}
        <div class="max-w-lg space-y-4">
            <flux:callout variant="info" icon="information-circle">
                <flux:callout.heading>{{ __('page.grant-agreement-donor.readonly-heading') }}</flux:callout.heading>
                <flux:callout.text>{{ __('page.grant-agreement-donor.readonly-description') }}</flux:callout.text>
            </flux:callout>

            @if ($name)
                <div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-name') }}</flux:text>
                        <flux:text>{{ $name }}</flux:text>
                    </div>

                    @if ($origin)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-origin') }}</flux:text>
                            <flux:text>{{ $origin }}</flux:text>
                        </div>
                    @endif

                    @if ($country)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-country') }}</flux:text>
                            <flux:text>{{ $country }}</flux:text>
                        </div>
                    @endif

                    @if ($origin === 'DALAM NEGERI' && $grant->donor)
                        @if ($grant->donor->nama_provinsi)
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-province') }}</flux:text>
                                <flux:text>{{ $grant->donor->nama_provinsi }}</flux:text>
                            </div>
                        @endif

                        @if ($grant->donor->nama_kabupaten_kota)
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-regency') }}</flux:text>
                                <flux:text>{{ $grant->donor->nama_kabupaten_kota }}</flux:text>
                            </div>
                        @endif

                        @if ($grant->donor->nama_kecamatan)
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-district') }}</flux:text>
                                <flux:text>{{ $grant->donor->nama_kecamatan }}</flux:text>
                            </div>
                        @endif

                        @if ($grant->donor->nama_desa_kelurahan)
                            <div>
                                <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-village') }}</flux:text>
                                <flux:text>{{ $grant->donor->nama_desa_kelurahan }}</flux:text>
                            </div>
                        @endif
                    @endif

                    @if ($address)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-address') }}</flux:text>
                            <flux:text>{{ $address }}</flux:text>
                        </div>
                    @endif

                    @if ($category)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-category') }}</flux:text>
                            <flux:text>{{ $category }}</flux:text>
                        </div>
                    @endif

                    @if ($phone)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-phone') }}</flux:text>
                            <flux:text>{{ $phone }}</flux:text>
                        </div>
                    @endif

                    @if ($email)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">{{ __('page.grant-planning-donor.label-email') }}</flux:text>
                            <flux:text>{{ $email }}</flux:text>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex items-center gap-4">
                <flux:button variant="primary" wire:click="save">{{ __('common.continue') }}</flux:button>
                <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
            </div>
        </div>
    @else
        {{-- Editable form for direct agreements --}}
        <form wire:submit="save" class="max-w-lg space-y-6">
            <div class="relative">
                <flux:input
                    wire:model.live.debounce.300ms="name"
                    :label="__('page.grant-planning-donor.label-name')"
                    :placeholder="__('page.grant-planning-donor.placeholder-name')"
                    type="text"
                    class:input="uppercase"
                    x-on:input="$event.target.value = $event.target.value.toUpperCase()"
                    required
                />

                @if ($matchingDonors->isNotEmpty())
                    <div class="absolute z-10 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        @foreach ($matchingDonors as $match)
                            <button
                                type="button"
                                wire:click="selectDonor({{ $match->id }})"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-zinc-100 first:rounded-t-lg last:rounded-b-lg dark:hover:bg-zinc-700"
                            >
                                <flux:icon.user class="size-4 text-zinc-400" />
                                <span>{{ $match->nama }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <flux:input wire:model="phone" :label="__('page.grant-planning-donor.label-phone')" type="tel" required />
            <flux:input wire:model="email" :label="__('page.grant-planning-donor.label-email')" type="email" class:input="lowercase" x-on:input="$event.target.value = $event.target.value.toLowerCase()" />

            <flux:radio.group wire:model.live="origin" :label="__('page.grant-planning-donor.label-origin')" variant="segmented">
                @foreach (config('options.donor_origins') as $originOption)
                    <flux:radio :value="$originOption" :label="$originOption" />
                @endforeach
            </flux:radio.group>

            @if ($origin === 'LUAR NEGERI')
                <flux:select
                    variant="combobox"
                    wire:model="country"
                    :label="__('page.grant-planning-donor.label-country')"
                    :placeholder="__('page.grant-planning-donor.placeholder-country')"
                >
                    @foreach (config('options.countries') as $countryOption)
                        <flux:select.option :value="$countryOption">{{ $countryOption }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            @if ($origin === 'DALAM NEGERI')
                <flux:select
                    variant="combobox"
                    wire:model.live="provinceId"
                    :label="__('page.grant-planning-donor.label-province')"
                    :placeholder="__('page.grant-planning-donor.placeholder-province')"
                >
                    @foreach ($provinceOptions as $province)
                        <flux:select.option :value="$province['id']">{{ $province['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                @if ($provinceId)
                    <flux:select
                        variant="combobox"
                        wire:model.live="regencyId"
                        :label="__('page.grant-planning-donor.label-regency')"
                        :placeholder="__('page.grant-planning-donor.placeholder-regency')"
                    >
                        @foreach ($regencyOptions as $regency)
                            <flux:select.option :value="$regency['id']">{{ $regency['name'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                @if ($regencyId)
                    <flux:select
                        variant="combobox"
                        wire:model.live="districtId"
                        :label="__('page.grant-planning-donor.label-district')"
                        :placeholder="__('page.grant-planning-donor.placeholder-district')"
                    >
                        @foreach ($districtOptions as $district)
                            <flux:select.option :value="$district['id']">{{ $district['name'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                @if ($districtId)
                    <flux:select
                        variant="combobox"
                        wire:model.live="villageId"
                        :label="__('page.grant-planning-donor.label-village')"
                        :placeholder="__('page.grant-planning-donor.placeholder-village')"
                    >
                        @foreach ($villageOptions as $village)
                            <flux:select.option :value="$village['id']">{{ $village['name'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            @endif

            <flux:textarea wire:model="address" :label="__('page.grant-planning-donor.label-address')" rows="3" class="uppercase" x-on:input="$event.target.value = $event.target.value.toUpperCase()" required />

            @if ($origin)
                <flux:select
                    variant="combobox"
                    wire:model="category"
                    :label="__('page.grant-planning-donor.label-category')"
                    :placeholder="__('page.grant-planning-donor.placeholder-category')"
                >
                    @foreach ($categoryOptions as $cat)
                        <flux:select.option :value="$cat">{{ $cat }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
                <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
            </div>
        </form>
    @endif
</x-grant-agreement.step-layout>
