<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('page.confirm-password.title')"
            :description="__('page.confirm-password.description')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('common.password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('common.password')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('common.confirm') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
