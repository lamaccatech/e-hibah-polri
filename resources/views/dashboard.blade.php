<x-layouts::app :title="__('common.dashboard')">
    @if (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::SatuanKerja)
        <div x-data="{ view: 'type' }" class="grid min-h-[calc(100vh-10rem)] place-content-center">
            {{-- Step 1: Grant type selection --}}
            <div x-show="view === 'type'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <flux:heading size="xl" class="mb-8 text-center">{{ __('page.dashboard.title-grant-type') }}</flux:heading>

                <div class="grid gap-6 md:grid-cols-2">
                    <button @click="view = 'direct'"
                        class="group flex min-h-72 flex-col items-center justify-center gap-4 rounded-xl border border-zinc-200 px-10 py-16 text-center transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-500 dark:hover:bg-zinc-800/50">
                        <flux:icon.document-text class="size-20 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                        <div>
                            <flux:heading size="lg">{{ __('page.dashboard.direct-grant-title') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('page.dashboard.direct-grant-description') }}</flux:text>
                        </div>
                    </button>

                    <div class="relative flex min-h-72 flex-col items-center justify-center gap-4 rounded-xl border border-zinc-200 px-10 py-16 text-center opacity-50 dark:border-zinc-700">
                        <flux:badge size="sm" color="zinc" class="absolute top-3 right-3">{{ __('page.dashboard.planned-grant-badge') }}</flux:badge>
                        <flux:icon.document-check class="size-20 text-zinc-400" />
                        <div>
                            <flux:heading size="lg">{{ __('page.dashboard.planned-grant-title') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('page.dashboard.planned-grant-description') }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Direct grant sub-options --}}
            <div x-show="view === 'direct'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="mb-8 flex items-center justify-center gap-3">
                    <flux:button variant="ghost" icon="arrow-left" @click="view = 'type'" />
                    <flux:heading size="xl">{{ __('page.dashboard.title-direct-options') }}</flux:heading>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <a href="{{ route('grant-planning.create') }}" wire:navigate
                        class="group flex min-h-72 flex-col items-center justify-center gap-4 rounded-xl border border-zinc-200 px-10 py-16 text-center transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-500 dark:hover:bg-zinc-800/50">
                        <flux:icon.document-text class="size-20 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                        <div>
                            <flux:heading size="lg">{{ __('page.dashboard.proposal-title') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('page.dashboard.proposal-description') }}</flux:text>
                        </div>
                    </a>

                    <a href="{{ route('grant-agreement.create') }}" wire:navigate
                        class="group flex min-h-72 flex-col items-center justify-center gap-4 rounded-xl border border-zinc-200 px-10 py-16 text-center transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-500 dark:hover:bg-zinc-800/50">
                        <flux:icon.document-check class="size-20 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                        <div>
                            <flux:heading size="lg">{{ __('page.dashboard.agreement-title') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('page.dashboard.agreement-description') }}</flux:text>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    @elseif (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::SatuanInduk)
        @livewire('polda-dashboard')
    @elseif (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::Mabes)
        @livewire('mabes-dashboard')
    @endif
</x-layouts::app>
