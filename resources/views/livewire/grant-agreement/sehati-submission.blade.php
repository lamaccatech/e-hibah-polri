<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-sehati.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-6">
        {{-- Penerima Hibah --}}
        <flux:select wire:model="grantRecipient" variant="combobox" :label="__('page.grant-agreement-sehati.label-grant-recipient')" :placeholder="__('page.grant-agreement-sehati.label-grant-recipient')">
            <x-slot name="input">
                <flux:select.input wire:model="grantRecipientSearch" />
            </x-slot>
            @foreach ($grantRecipientOptions as $option)
                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
            @endforeach
            <flux:select.option.create wire:click="createOption('grantRecipient')" min-length="2">
                {{ __('common.create') }} "<span wire:text="grantRecipientSearch"></span>"
            </flux:select.option.create>
        </flux:select>

        {{-- Sumber Pembiayaan --}}
        <flux:select wire:model="fundingSource" variant="combobox" :label="__('page.grant-agreement-sehati.label-funding-source')" :placeholder="__('page.grant-agreement-sehati.label-funding-source')">
            <x-slot name="input">
                <flux:select.input wire:model="fundingSourceSearch" />
            </x-slot>
            @foreach ($fundingSourceOptions as $option)
                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
            @endforeach
            <flux:select.option.create wire:click="createOption('fundingSource')" min-length="2">
                {{ __('common.create') }} "<span wire:text="fundingSourceSearch"></span>"
            </flux:select.option.create>
        </flux:select>

        {{-- Jenis Pembiayaan --}}
        <flux:select wire:model="fundingType" variant="combobox" :label="__('page.grant-agreement-sehati.label-funding-type')" :placeholder="__('page.grant-agreement-sehati.label-funding-type')">
            <x-slot name="input">
                <flux:select.input wire:model="fundingTypeSearch" />
            </x-slot>
            @foreach ($fundingTypeOptions as $option)
                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
            @endforeach
            <flux:select.option.create wire:click="createOption('fundingType')" min-length="2">
                {{ __('common.create') }} "<span wire:text="fundingTypeSearch"></span>"
            </flux:select.option.create>
        </flux:select>

        {{-- Cara Penarikan --}}
        <flux:select wire:model="withdrawalMethod" variant="combobox" :label="__('page.grant-agreement-sehati.label-withdrawal-method')" :placeholder="__('page.grant-agreement-sehati.label-withdrawal-method')">
            <x-slot name="input">
                <flux:select.input wire:model="withdrawalMethodSearch" />
            </x-slot>
            @foreach ($withdrawalMethodOptions as $option)
                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
            @endforeach
            <flux:select.option.create wire:click="createOption('withdrawalMethod')" min-length="2">
                {{ __('common.create') }} "<span wire:text="withdrawalMethodSearch"></span>"
            </flux:select.option.create>
        </flux:select>

        {{-- Dates --}}
        <flux:date-picker wire:model="effectiveDate" :label="__('page.grant-agreement-sehati.label-effective-date')" locale="id-ID" />
        <flux:date-picker wire:model="withdrawalDeadline" :label="__('page.grant-agreement-sehati.label-withdrawal-deadline')" locale="id-ID" />
        <flux:date-picker wire:model="accountClosingDate" :label="__('page.grant-agreement-sehati.label-account-closing-date')" locale="id-ID" />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-detail.show', $grant)" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</div>
