<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.user-create.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
        <flux:input wire:model="email" :label="__('common.email')" type="email" required />

        <flux:input wire:model="password" :label="__('common.password')" type="password" required viewable />

        <flux:input wire:model="passwordConfirmation" :label="__('page.user-create.label-password-confirmation')" type="password" required viewable />

        <flux:separator />

        <flux:input wire:model="unitName" :label="__('page.user-create.label-unit-name')" type="text" required />

        <flux:input wire:model="code" :label="__('page.user-create.label-unit-code')" type="text" required />

        <flux:select wire:model="unitLevel" :label="__('page.user-create.label-unit-level')" placeholder="{{ __('page.user-create.placeholder-level') }}">
            @foreach ($unitLevels as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="parentUnitId" :label="__('page.user-create.label-parent-unit')" placeholder="{{ __('page.user-create.placeholder-parent-unit') }}">
            @foreach ($parentUnits as $unit)
                <flux:select.option :value="$unit->id_user">{{ $unit->nama_unit }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('user.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</div>
