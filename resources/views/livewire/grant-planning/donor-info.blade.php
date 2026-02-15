<x-grant-planning.step-layout :grant="$grant" :currentStep="2">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-planning-donor.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
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
                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 first:rounded-t-lg last:rounded-b-lg"
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
            <flux:button variant="ghost" :href="route('grant-planning.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-planning.step-layout>
