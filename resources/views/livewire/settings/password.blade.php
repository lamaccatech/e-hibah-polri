<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('page.password.sr-title') }}</flux:heading>

    <x-settings.layout :heading="__('page.password.title')" :subheading="__('page.password.description')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('page.password.label-current')"
                type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                :label="__('page.password.label-new')"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('page.password.label-confirm')"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('common.save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('common.saved') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
