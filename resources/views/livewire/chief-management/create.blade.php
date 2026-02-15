<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.chief-create.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
        <flux:input wire:model="fullName" :label="__('page.chief-create.label-name')" type="text" required />

        <flux:input wire:model="position" :label="__('page.chief-create.label-position')" type="text" required />

        <flux:input wire:model="rank" :label="__('page.chief-create.label-rank')" type="text" required />

        <flux:input wire:model="nrp" :label="__('page.chief-create.label-nrp')" type="text" required />

        <flux:field>
            <flux:label>{{ __('page.chief-create.label-signature') }}</flux:label>
            <input type="file" wire:model="signature" accept="image/*" required />
            <flux:error name="signature" />
        </flux:field>

        @if ($signature)
            <img src="{{ $signature->temporaryUrl() }}" class="h-20" alt="{{ __('page.chief-create.signature-preview') }}">
        @endif

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('chief.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</div>
