<x-grant-planning.step-layout :grant="$grant" :currentStep="3">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-planning-proposal.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-8 max-w-3xl">
        {{-- Chapters --}}
        <div class="space-y-6">
            @foreach ($planningChapters as $chapter)
                <div class="space-y-3 p-4 border rounded-lg border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="xl" class="font-bold">{{ $chapter->label() }}</flux:heading>

                    @foreach ($chapter->prompts() as $promptIndex => $prompt)
                        <flux:editor
                            wire:model="chapters.{{ $chapter->value }}.{{ $promptIndex }}"
                            :label="$prompt"
                            toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                        />
                    @endforeach

                    @if (empty($chapter->prompts()))
                        <flux:editor
                            wire:model="chapters.{{ $chapter->value }}.0"
                            toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                        />
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Budget Plan --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('page.grant-planning-proposal.section-budget') }}</flux:heading>

            <flux:input wire:model="currency" :label="__('page.grant-planning-proposal.label-currency')" type="text" class="max-w-xs" />

            @foreach ($budgetItems as $index => $item)
                <div class="p-4 border rounded-lg border-zinc-200 dark:border-zinc-700 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">#{{ $index + 1 }}</span>
                        @if (count($budgetItems) > 1)
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeBudgetItem({{ $index }})" />
                        @endif
                    </div>
                    <flux:input wire:model="budgetItems.{{ $index }}.uraian" :label="__('page.grant-planning-proposal.label-description')" type="text" />
                    <div class="grid grid-cols-3 gap-3">
                        <flux:input wire:model="budgetItems.{{ $index }}.volume" :label="__('page.grant-planning-proposal.label-volume')" type="number" step="0.01" />
                        <flux:input wire:model="budgetItems.{{ $index }}.satuan" :label="__('page.grant-planning-proposal.label-unit')" type="text" />
                        <flux:input wire:model="budgetItems.{{ $index }}.harga_satuan" :label="__('page.grant-planning-proposal.label-unit-price')" type="number" step="0.01" />
                    </div>
                </div>
            @endforeach

            <flux:button variant="ghost" icon="plus" wire:click="addBudgetItem">
                {{ __('page.grant-planning-proposal.add-budget-item') }}
            </flux:button>
        </div>

        {{-- Activity Schedule --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('page.grant-planning-proposal.section-schedule') }}</flux:heading>

            @foreach ($schedules as $index => $schedule)
                <div class="p-4 border rounded-lg border-zinc-200 dark:border-zinc-700 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">#{{ $index + 1 }}</span>
                        @if (count($schedules) > 1)
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeSchedule({{ $index }})" />
                        @endif
                    </div>
                    <flux:input wire:model="schedules.{{ $index }}.uraian_kegiatan" :label="__('page.grant-planning-proposal.label-activity')" type="text" />
                    <div class="grid grid-cols-2 gap-3">
                        <flux:input wire:model="schedules.{{ $index }}.tanggal_mulai" :label="__('page.grant-planning-proposal.label-start-date')" type="date" />
                        <flux:input wire:model="schedules.{{ $index }}.tanggal_selesai" :label="__('page.grant-planning-proposal.label-end-date')" type="date" />
                    </div>
                </div>
            @endforeach

            <flux:button variant="ghost" icon="plus" wire:click="addSchedule">
                {{ __('page.grant-planning-proposal.add-schedule') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-planning.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-planning.step-layout>
