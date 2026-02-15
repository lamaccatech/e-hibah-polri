<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('component.navbar.nav-dashboard') }}
                </flux:navbar.item>

                @if (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::SatuanKerja)
                    <flux:navbar.item icon="document-text" :href="route('grant-planning.index')" :current="request()->routeIs('grant-planning.*')" wire:navigate>
                        {{ __('component.navbar.nav-grant-planning') }}
                    </flux:navbar.item>
                    <flux:navbar.item icon="user-circle" :href="route('chief.index')" :current="request()->routeIs('chief.*')" wire:navigate>
                        {{ __('component.navbar.nav-chief-management') }}
                    </flux:navbar.item>
                @endif

                @if (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::Mabes)
                    <flux:navbar.item icon="users" :href="route('user.index')" :current="request()->routeIs('user.*')" wire:navigate>
                        {{ __('component.navbar.nav-user-management') }}
                    </flux:navbar.item>
                @endif
            </flux:navbar>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
                            {{ __('common.settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="logout-button">
                            {{ __('common.logout') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('component.navbar.nav-dashboard') }}
                </flux:sidebar.item>

                @if (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::SatuanKerja)
                    <flux:sidebar.item icon="document-text" :href="route('grant-planning.index')" :current="request()->routeIs('grant-planning.*')" wire:navigate>
                        {{ __('component.navbar.nav-grant-planning') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-circle" :href="route('chief.index')" :current="request()->routeIs('chief.*')" wire:navigate>
                        {{ __('component.navbar.nav-chief-management') }}
                    </flux:sidebar.item>
                @endif

                @if (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::Mabes)
                    <flux:sidebar.item icon="users" :href="route('user.index')" :current="request()->routeIs('user.*')" wire:navigate>
                        {{ __('component.navbar.nav-user-management') }}
                    </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate>
                    {{ __('common.settings') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @livewireScripts
        @fluxScripts
    </body>
</html>
