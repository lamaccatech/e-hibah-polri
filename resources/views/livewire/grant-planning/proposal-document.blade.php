<x-grant-planning.step-layout :grant="$grant" :currentStep="3">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-planning-proposal.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-8 max-w-3xl">
        {{-- Chapters --}}
        <div class="space-y-8">
            @foreach ($planningChapters as $chapter)
                @if ($chapter === \App\Enums\ProposalChapter::Purpose)
                    <div class="space-y-3 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="xl" class="font-bold">{{ $chapter->label() }}</flux:heading>
                        <div class="prose dark:prose-invert text-sm uppercase">
                            {!! $chapters[$chapter->value][0] ?? '' !!}
                        </div>
                    </div>
                @elseif ($chapter === \App\Enums\ProposalChapter::Objective)
                    <div class="space-y-3 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="xl" class="font-bold">{{ $chapter->label() }}</flux:heading>

                        @foreach ($objectives as $index => $objective)
                            <div wire:key="objective-{{ $index }}" class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">#{{ $index + 1 }}</span>
                                    @if (count($objectives) > 1)
                                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeObjective({{ $index }})" />
                                    @endif
                                </div>

                                <flux:select
                                    variant="combobox"
                                    wire:model="objectives.{{ $index }}.purpose"
                                    :placeholder="__('page.grant-planning-proposal.placeholder-purpose')"
                                >
                                    @foreach (config('options.grant_purposes') as $purpose)
                                        <flux:select.option :value="$purpose">{{ $purpose }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:editor
                                    wire:model="objectives.{{ $index }}.detail"
                                    toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                                />
                                <flux:error name="objectives.{{ $index }}.detail" />
                            </div>
                        @endforeach

                        <flux:button variant="ghost" icon="plus" wire:click="addObjective">
                            {{ __('page.grant-planning-proposal.add-objective') }}
                        </flux:button>
                    </div>
                @elseif ($chapter === \App\Enums\ProposalChapter::BudgetPlan)
                    {{-- Budget Plan with currency + budget items --}}
                    <div class="space-y-3 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="xl" class="font-bold">{{ $chapter->label() }}</flux:heading>

                        <flux:select
                            variant="combobox"
                            wire:model="currency"
                            :label="__('page.grant-planning-proposal.label-currency')"
                        >
                            @foreach ($currencyOptions as $option)
                                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        @foreach ($budgetItems as $index => $item)
                            <div class="p-4 border rounded-lg border-zinc-200 dark:border-zinc-700 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium">#{{ $index + 1 }}</span>
                                    @if (count($budgetItems) > 1)
                                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeBudgetItem({{ $index }})" />
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:input wire:model="budgetItems.{{ $index }}.uraian" :label="__('page.grant-planning-proposal.label-description')" type="text" />
                                    <div>
                                        <flux:input
                                            :label="__('page.grant-planning-proposal.label-value')"
                                            type="text"
                                            inputmode="numeric"
                                            value="{{ $item['nilai'] ? number_format((float) $item['nilai'], 0, ',', '.') : '' }}"
                                            x-on:input="
                                                let raw = $event.target.value.replace(/\D/g, '');
                                                $event.target.value = raw ? new Intl.NumberFormat('id-ID').format(Number(raw)) : '';
                                                $wire.set('budgetItems.{{ $index }}.nilai', raw);
                                            "
                                        />
                                        <flux:error name="budgetItems.{{ $index }}.nilai" />
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <flux:button variant="ghost" icon="plus" wire:click="addBudgetItem">
                            {{ __('page.grant-planning-proposal.add-budget-item') }}
                        </flux:button>
                    </div>
                @else
                    <div class="space-y-3 {{ $chapter === \App\Enums\ProposalChapter::ImplementationPlan ? '' : 'pb-6 border-b border-zinc-200 dark:border-zinc-700' }}">
                        <flux:heading size="xl" class="font-bold">{{ $chapter->label() }}</flux:heading>

                        @foreach ($chapter->prompts() as $promptIndex => $prompt)
                            <div>
                                <flux:editor
                                    wire:model="chapters.{{ $chapter->value }}.{{ $promptIndex }}"
                                    :label="$prompt"
                                    toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                                />
                                <flux:error name="chapters.{{ $chapter->value }}.{{ $promptIndex }}" />
                            </div>
                        @endforeach

                        @if (empty($chapter->prompts()))
                            <div>
                                <flux:editor
                                    wire:model="chapters.{{ $chapter->value }}.0"
                                    toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                                />
                                <flux:error name="chapters.{{ $chapter->value }}.0" />
                            </div>
                        @endif
                    </div>

                    {{-- Schedules inline after ImplementationPlan --}}
                    @if ($chapter === \App\Enums\ProposalChapter::ImplementationPlan)
                        <div class="space-y-4 pb-6 border-b border-zinc-200 dark:border-zinc-700">
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
                                        <flux:date-picker wire:model="schedules.{{ $index }}.tanggal_mulai" :label="__('page.grant-planning-proposal.label-start-date')" locale="id-ID" />
                                        <flux:date-picker wire:model="schedules.{{ $index }}.tanggal_selesai" :label="__('page.grant-planning-proposal.label-end-date')" locale="id-ID" />
                                    </div>
                                </div>
                            @endforeach

                            <flux:button variant="ghost" icon="plus" wire:click="addSchedule">
                                {{ __('page.grant-planning-proposal.add-schedule') }}
                            </flux:button>
                        </div>
                    @endif
                @endif
            @endforeach
        </div>

        {{-- Custom Chapters --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('page.grant-planning-proposal.section-custom-chapters') }}</flux:heading>

            @foreach ($customChapters as $chapterIndex => $customChapter)
                <div class="p-4 border rounded-lg border-zinc-200 dark:border-zinc-700 space-y-3">
                    <div class="flex justify-between items-center">
                        <flux:input
                            wire:model="customChapters.{{ $chapterIndex }}.title"
                            :label="__('page.grant-planning-proposal.label-chapter-title')"
                            type="text"
                            class="flex-1"
                        />
                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeCustomChapter({{ $chapterIndex }})" class="ml-2 mt-6" />
                    </div>

                    @foreach ($customChapter['paragraphs'] as $paragraphIndex => $paragraph)
                        <div class="space-y-1">
                            <flux:editor
                                wire:model="customChapters.{{ $chapterIndex }}.paragraphs.{{ $paragraphIndex }}"
                                toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                            />
                            <flux:error name="customChapters.{{ $chapterIndex }}.paragraphs.{{ $paragraphIndex }}" />
                            @if (count($customChapter['paragraphs']) > 1)
                                <div class="flex justify-end">
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeCustomChapterParagraph({{ $chapterIndex }}, {{ $paragraphIndex }})" />
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <flux:button variant="ghost" size="sm" icon="plus" wire:click="addCustomChapterParagraph({{ $chapterIndex }})">
                        {{ __('page.grant-planning-proposal.add-paragraph') }}
                    </flux:button>
                </div>
            @endforeach

            <flux:button variant="ghost" icon="plus" wire:click="addCustomChapter">
                {{ __('page.grant-planning-proposal.add-custom-chapter') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-planning.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-planning.step-layout>
