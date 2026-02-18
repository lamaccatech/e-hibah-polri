@if ($changeHistory->isEmpty())
    <flux:text class="text-center py-8">{{ __('page.grant-detail-change-history.empty-state') }}</flux:text>
@else
    <div class="space-y-4">
        @foreach ($changeHistory as $entry)
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" wire:key="change-{{ $entry->id }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <flux:text class="text-sm font-medium">
                            {{ $entry->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                        </flux:text>
                        <flux:badge size="sm">
                            {{ __('page.grant-detail-change-history.label-user') }}:
                            {{ $entry->user?->name ?? __('page.grant-detail-change-history.label-system') }}
                        </flux:badge>
                    </div>
                </div>

                <flux:text class="text-sm mb-3">
                    <span class="font-medium">{{ __('page.grant-detail-change-history.label-reason') }}:</span>
                    {{ $entry->change_reason }}
                </flux:text>

                @php
                    $changes = $entry->getChanges();
                @endphp

                @if (count($changes) > 0)
                    <details class="mt-2">
                        <summary class="cursor-pointer text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200">
                            {{ __('page.grant-detail-change-history.label-changes') }} ({{ count($changes) }})
                        </summary>
                        <div class="mt-2 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                        <th class="py-1 pr-4 text-left font-medium text-zinc-500">Field</th>
                                        <th class="py-1 pr-4 text-left font-medium text-zinc-500">{{ __('page.grant-detail-change-history.label-from') }}</th>
                                        <th class="py-1 text-left font-medium text-zinc-500">{{ __('page.grant-detail-change-history.label-to') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($changes as $field => $diff)
                                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                            <td class="py-1.5 pr-4 font-mono text-xs text-zinc-600 dark:text-zinc-400">{{ $field }}</td>
                                            <td class="py-1.5 pr-4 text-red-600 dark:text-red-400">
                                                @if ($diff['from'] === null)
                                                    <span class="italic text-zinc-400">—</span>
                                                @else
                                                    {{ is_array($diff['from']) ? json_encode($diff['from']) : $diff['from'] }}
                                                @endif
                                            </td>
                                            <td class="py-1.5 text-green-600 dark:text-green-400">
                                                @if ($diff['to'] === null)
                                                    <span class="italic text-zinc-400">—</span>
                                                @else
                                                    {{ is_array($diff['to']) ? json_encode($diff['to']) : $diff['to'] }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif
            </div>
        @endforeach
    </div>
@endif
