<div class="max-w-3xl space-y-8">
    @foreach ($documentHistory as $slug => $group)
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <flux:heading size="lg" class="mb-4">{{ $group['label'] }}</flux:heading>

            @if ($group['documents']->isNotEmpty())
                <div class="relative space-y-6">
                    <div class="absolute top-2 bottom-2 left-[7px] w-px bg-zinc-200 dark:bg-zinc-700"></div>

                    @foreach ($group['documents'] as $index => $doc)
                        <div class="relative flex gap-3">
                            @if ($index === 0)
                                <div class="mt-1.5 size-[15px] shrink-0 rounded-full border-2 border-accent bg-accent z-10"></div>
                            @else
                                <div class="mt-1.5 size-[15px] shrink-0 rounded-full border-2 border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 z-10"></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ __('page.grant-detail.document-date-label') }}: {{ $doc->tanggal }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                            {{ __('page.grant-detail.generated-at') }} {{ $doc->created_at->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="arrow-down-tray"
                                        :href="route('grant-document.download', [$grant, $doc])"
                                    />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text class="text-zinc-400">{{ __('page.grant-detail.no-document-generated') }}</flux:text>
            @endif
        </div>
    @endforeach
</div>
