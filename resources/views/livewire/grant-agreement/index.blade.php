<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('page.grant-agreement.title') }}</flux:heading>
    </div>

    @error('submit')
        <div class="mb-4">
            <flux:badge color="red">{{ $message }}</flux:badge>
        </div>
    @enderror

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('page.grant-agreement.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-agreement.column-donor') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-agreement.column-status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.grant-agreement.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($grants as $grant)
                <flux:table.row :key="$grant->id">
                    <flux:table.cell>{{ $grant->nama_hibah }}</flux:table.cell>
                    <flux:table.cell>{{ $grant->donor?->nama ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$grant->statusHistory->last()?->status_sesudah?->isRejected() ? 'red' : ($grant->statusHistory->last()?->status_sesudah?->isRevisionRequested() ? 'yellow' : null)">
                            {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="eye" :href="route('grant-detail.show', $grant)" wire:navigate />
                            @if (in_array($grant->id, $editableIds))
                                <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('grant-agreement.reception-basis', $grant)" wire:navigate />
                            @endif
                            @if (in_array($grant->id, $submittableIds))
                                <flux:button variant="primary" size="sm" wire:click="confirmSubmit({{ $grant->id }})">
                                    {{ __('page.grant-agreement.submit-button') }}
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center">
                        {{ __('page.grant-agreement.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if (count($submittableIds) > 0)
    <flux:modal wire:model.self="showSubmitModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('page.grant-agreement.submit-confirm-title') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('page.grant-agreement.submit-confirm-description') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" wire:click="submit">
                    {{ __('page.grant-agreement.submit-button') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
    @endif
</div>
