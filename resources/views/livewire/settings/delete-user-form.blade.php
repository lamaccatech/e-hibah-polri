<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('page.delete-account.title') }}</flux:heading>
        <flux:subheading>{{ __('page.delete-account.description') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('page.delete-account.title') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('page.delete-account.modal-title') }}</flux:heading>

                <flux:subheading>
                    {{ __('page.delete-account.modal-description') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('common.password')" type="password" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('page.delete-account.title') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
