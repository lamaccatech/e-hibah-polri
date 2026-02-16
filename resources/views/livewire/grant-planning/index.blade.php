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
                        <div class="flex flex-col gap-1">
                            <flux:badge size="sm">
                                {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                            </flux:badge>
                            @if ($grant->numberings->where('tahapan', \App\Enums\GrantStage::Planning)->first())
                                @php $planningNumber = $grant->numberings->where('tahapan', \App\Enums\GrantStage::Planning)->first()->nomor; @endphp
                                <div class="flex items-center gap-1">
                                    <flux:text size="xs" class="font-mono">{{ $planningNumber }}</flux:text>
                                    <flux:button
                                        variant="subtle"
                                        size="xs"
                                        icon="clipboard-document"
                                        x-on:click="
                                            navigator.clipboard.writeText('{{ $planningNumber }}');
                                            $el.querySelector('svg').classList.add('text-green-500');
                                            setTimeout(() => $el.querySelector('svg').classList.remove('text-green-500'), 1500);
                                        "
                                    />
                                </div>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
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
