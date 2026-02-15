<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('page.forgot-password.title')" :description="__('page.forgot-password.description')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('common.email')"
                type="email"
                required
                autofocus
                :placeholder="__('page.login.placeholder-email')"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('page.forgot-password.submit-button') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('page.forgot-password.return-text') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('page.forgot-password.login-link') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
