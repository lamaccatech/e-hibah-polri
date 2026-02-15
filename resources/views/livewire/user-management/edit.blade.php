<div>
    <flux:heading size="xl" class="mb-6">{{ __('Edit User') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
        <flux:input wire:model="email" :label="__('Email')" type="email" required />

        <flux:separator />

        <flux:input wire:model="unitName" :label="__('Nama Unit')" type="text" required />

        <flux:input wire:model="code" :label="__('Kode Unit')" type="text" required />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Simpan') }}</flux:button>
            <flux:button variant="ghost" :href="route('user.index')" wire:navigate>{{ __('Batal') }}</flux:button>
        </div>
    </form>
</div>
