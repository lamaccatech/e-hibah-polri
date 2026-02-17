<div class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    {{-- Section 1: Planning Stats --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('page.dashboard.polda-planning-heading') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-blue-50 p-2.5 dark:bg-blue-900/30">
                    <flux:icon.document-text class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-planning-created') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningCreated'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-amber-50 p-2.5 dark:bg-amber-900/30">
                    <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-planning-unprocessed') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningUnprocessed'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-indigo-50 p-2.5 dark:bg-indigo-900/30">
                    <flux:icon.arrow-path class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-planning-processing') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningProcessing'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-red-50 p-2.5 dark:bg-red-900/30">
                    <flux:icon.x-circle class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-planning-rejected') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningRejected'] }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 2: Agreement Stats --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('page.dashboard.polda-agreement-heading') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-blue-50 p-2.5 dark:bg-blue-900/30">
                    <flux:icon.document-check class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-agreement-created') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementCreated'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-amber-50 p-2.5 dark:bg-amber-900/30">
                    <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-agreement-unprocessed') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementUnprocessed'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-indigo-50 p-2.5 dark:bg-indigo-900/30">
                    <flux:icon.arrow-path class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-agreement-processing') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementProcessing'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-red-50 p-2.5 dark:bg-red-900/30">
                    <flux:icon.x-circle class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.polda-agreement-rejected') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementRejected'] }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3: Inbox --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('page.dashboard.polda-inbox-heading') }}</flux:heading>

        <flux:table :paginate="$inbox">
            <flux:table.columns>
                <flux:table.column>{{ __('page.dashboard.polda-inbox-column-name') }}</flux:table.column>
                <flux:table.column>{{ __('page.dashboard.polda-inbox-column-unit') }}</flux:table.column>
                <flux:table.column>{{ __('page.dashboard.polda-inbox-column-stage') }}</flux:table.column>
                <flux:table.column>{{ __('page.dashboard.polda-inbox-column-status') }}</flux:table.column>
                <flux:table.column align="end">{{ __('page.dashboard.polda-inbox-column-action') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($inbox as $grant)
                    <flux:table.row :key="$grant->id">
                        <flux:table.cell>{{ $grant->nama_hibah }}</flux:table.cell>
                        <flux:table.cell>{{ $grant->orgUnit?->nama_unit ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $grant->tahapan->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm">
                                {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($grant->tahapan === \App\Enums\GrantStage::Planning)
                                <flux:button variant="ghost" size="sm" icon="eye" :href="route('grant-review.index')" wire:navigate />
                            @else
                                <flux:button variant="ghost" size="sm" icon="eye" :href="route('agreement-review.index')" wire:navigate />
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center">
                            {{ __('page.dashboard.polda-inbox-empty') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
