<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.user-edit.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
        <flux:input wire:model="email" :label="__('common.email')" type="email" required />

        <flux:separator />

        <flux:input wire:model="unitName" :label="__('page.user-edit.label-unit-name')" type="text" required />

        <flux:input wire:model="code" :label="__('page.user-edit.label-unit-code')" type="text" required />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('user.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</div>
