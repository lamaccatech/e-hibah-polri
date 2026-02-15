<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('page.reset-password.title')" :description="__('page.reset-password.description')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('common.email')"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('common.password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('common.password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('page.reset-password.label-confirm-password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('page.reset-password.label-confirm-password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('page.reset-password.submit-button') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
