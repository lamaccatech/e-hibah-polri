<div>
    <flux:heading size="xl" class="mb-6">{{ __('Tambah User') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
        <flux:input wire:model="email" :label="__('Email')" type="email" required />

        <flux:input wire:model="password" :label="__('Password')" type="password" required viewable />

        <flux:input wire:model="passwordConfirmation" :label="__('Konfirmasi Password')" type="password" required viewable />

        <flux:separator />

        <flux:input wire:model="unitName" :label="__('Nama Unit')" type="text" required />

        <flux:input wire:model="code" :label="__('Kode Unit')" type="text" required />

        <flux:select wire:model="unitLevel" :label="__('Level Unit')" placeholder="{{ __('Pilih level...') }}">
            @foreach ($unitLevels as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="parentUnitId" :label="__('Unit Atasan')" placeholder="{{ __('Pilih unit atasan...') }}">
            @foreach ($parentUnits as $unit)
                <flux:select.option :value="$unit->id_user">{{ $unit->nama_unit }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Simpan') }}</flux:button>
            <flux:button variant="ghost" :href="route('user.index')" wire:navigate>{{ __('Batal') }}</flux:button>
        </div>
    </form>
</div>
