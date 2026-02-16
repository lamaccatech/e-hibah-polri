<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('page.grant-planning.title') }}</flux:heading>
    </div>

    @error('submit')
        <div class="mb-4">
            <flux:badge color="red">{{ $message }}</flux:badge>
        </div>
    @enderror

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('page.grant-planning.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-planning.column-donor') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-planning.column-value') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-planning.column-status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.grant-planning.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($grants as $grant)
                <flux:table.row :key="$grant->id">
                    <flux:table.cell>{{ $grant->nama_hibah }}</flux:table.cell>
                    <flux:table.cell>{{ $grant->donor?->nama ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($grant->nilai_hibah)
                            {{ $grant->mata_uang }} {{ number_format($grant->nilai_hibah, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:badge size="sm">
                                {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                            </flux:badge>
                            @php $planningNumber = $grant->numberings->where('tahapan', \App\Enums\GrantStage::Planning)->first()?->nomor; @endphp
                            @if ($planningNumber)
                                <flux:tooltip toggleable>
                                    <flux:button icon="information-circle" size="sm" variant="ghost" class="shrink-0" />
                                    <flux:tooltip.content>
                                        <span
                                            class="inline-flex items-center gap-1.5 font-mono cursor-pointer"
                                            x-data="{ copied: false }"
                                            x-on:click.stop="
                                                navigator.clipboard.writeText('{{ $planningNumber }}');
                                                copied = true;
                                                setTimeout(() => copied = false, 1500);
                                            "
                                        >
                                            {{ $planningNumber }}
                                            <template x-if="!copied"><x-flux::icon.clipboard-document class="size-3.5" /></template>
                                            <template x-if="copied"><x-flux::icon.check class="size-3.5 text-green-500" /></template>
                                        </span>
                                    </flux:tooltip.content>
                                </flux:tooltip>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="eye" :href="route('grant-detail.show', $grant)" wire:navigate />
                            @if (in_array($grant->id, $editableIds))
                                <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('grant-planning.edit', $grant)" wire:navigate />
                            @endif
                            @if (in_array($grant->id, $submittableIds))
                                <flux:button variant="primary" size="sm" wire:click="confirmSubmit({{ $grant->id }})">
                                    {{ __('page.grant-planning.submit-button') }}
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">
                        {{ __('page.grant-planning.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if (count($submittableIds) > 0)
    <flux:modal wire:model.self="showSubmitModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('page.grant-planning.submit-confirm-title') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('page.grant-planning.submit-confirm-description') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" wire:click="submit">
                    {{ __('page.grant-planning.submit-button') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
    @endif
</div>
