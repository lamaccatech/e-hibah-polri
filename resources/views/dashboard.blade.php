<x-layouts::app :title="__('common.dashboard')">
    @if (auth()->user()->unit?->level_unit === \App\Enums\UnitLevel::SatuanKerja)
        <div class="grid min-h-[calc(100vh-10rem)] gap-6 md:grid-cols-2">
            <a href="{{ route('grant-planning.create') }}" wire:navigate
               class="group flex flex-col items-center justify-center gap-4 rounded-xl border border-zinc-200 p-10 text-center transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-500 dark:hover:bg-zinc-800/50">
                <flux:icon.document-text class="size-20 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                <div>
                    <flux:heading size="lg">{{ __('page.dashboard.planned-grant-title') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('page.dashboard.planned-grant-description') }}</flux:text>
                </div>
            </a>

            <div class="flex flex-col items-center justify-center gap-4 rounded-xl border border-zinc-200 p-10 text-center opacity-50 dark:border-zinc-700">
                <flux:icon.document-check class="size-20 text-zinc-400" />
                <div>
                    <flux:heading size="lg">{{ __('page.dashboard.direct-grant-title') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('page.dashboard.direct-grant-description') }}</flux:text>
                </div>
            </div>
        </div>
    @endif
</x-layouts::app>
