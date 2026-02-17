<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('page.grant-review.title') }}</flux:heading>
    </div>

    <flux:table :paginate="$grants">
        <flux:table.columns>
            <flux:table.column>{{ __('page.grant-review.column-unit') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-name') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-donor') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-value') }}</flux:table.column>
            <flux:table.column>{{ __('page.grant-review.column-status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('page.grant-review.column-action') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($grants as $grant)
                <flux:table.row :key="$grant->id">
                    <flux:table.cell>{{ $grant->orgUnit?->nama_unit ?? '-' }}</flux:table.cell>
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
                        <flux:badge size="sm">
                            {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="eye" :href="route('grant-detail.show', $grant)" wire:navigate />
                            @if (in_array($grant->id, $reviewableIds))
                                <flux:button variant="primary" size="sm" wire:click="confirmStartReview({{ $grant->id }})">
                                    {{ __('page.grant-review.start-review-button') }}
                                </flux:button>
                            @elseif (in_array($grant->id, $underReviewIds))
                                <flux:button variant="ghost" size="sm" icon="arrow-right" :href="route('grant-review.review', $grant)" wire:navigate>
                                    {{ __('page.grant-review.continue-review-button') }}
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center">
                        {{ __('page.grant-review.empty-state') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if (count($reviewableIds) > 0)
    <flux:modal wire:model.self="showStartReviewModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('page.grant-review.start-review-confirm-title') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('page.grant-review.start-review-confirm-description') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" wire:click="startReview">
                    {{ __('page.grant-review.start-review-button') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
    @endif
</div>
