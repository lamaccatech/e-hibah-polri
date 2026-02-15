<x-grant-planning.step-layout :grant="$grant" :currentStep="2">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-planning-donor.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
        <div class="flex gap-4">
            <flux:button
                :variant="$donorMode === 'existing' ? 'primary' : 'ghost'"
                wire:click.prevent="$set('donorMode', 'existing')"
            >
                {{ __('page.grant-planning-donor.mode-existing') }}
            </flux:button>
            <flux:button
                :variant="$donorMode === 'new' ? 'primary' : 'ghost'"
                wire:click.prevent="$set('donorMode', 'new')"
            >
                {{ __('page.grant-planning-donor.mode-new') }}
            </flux:button>
        </div>

        @if ($donorMode === 'existing')
            <flux:select wire:model="selectedDonorId" :label="__('page.grant-planning-donor.label-donor')" :placeholder="__('page.grant-planning-donor.placeholder-donor')">
                @foreach ($donors as $donor)
                    <flux:select.option :value="$donor->id">{{ $donor->nama }}</flux:select.option>
                @endforeach
            </flux:select>
        @else
            <flux:input wire:model="name" :label="__('page.grant-planning-donor.label-name')" type="text" required />
            <flux:input wire:model="origin" :label="__('page.grant-planning-donor.label-origin')" type="text" required />
            <flux:textarea wire:model="address" :label="__('page.grant-planning-donor.label-address')" rows="3" required />
            <flux:input wire:model="country" :label="__('page.grant-planning-donor.label-country')" type="text" required />
            <flux:input wire:model="category" :label="__('page.grant-planning-donor.label-category')" type="text" required />
        @endif

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-planning.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-planning.step-layout>
