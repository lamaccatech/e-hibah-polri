<x-grant-agreement.step-layout :grant="$grant" :currentStep="3">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-assessment.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-8">
        {{-- Mandatory Aspects --}}
        <div class="space-y-8">
            @foreach ($aspectCases as $aspect)
                <div class="space-y-3 border-b border-zinc-200 pb-6 dark:border-zinc-700">
                    <flux:heading size="xl" class="font-bold">{{ $aspect->label() }}</flux:heading>

                    @foreach ($aspect->prompts() as $promptIndex => $prompt)
                        <div>
                            <flux:editor
                                wire:model="mandatoryAspects.{{ $aspect->value }}.{{ $promptIndex }}"
                                :label="$prompt"
                                toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                            />
                            <flux:error name="mandatoryAspects.{{ $aspect->value }}.{{ $promptIndex }}" />
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Custom Aspects --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('page.grant-planning-assessment.section-custom') }}</flux:heading>

            @foreach ($customAspects as $aspectIndex => $customAspect)
                <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <flux:input
                            wire:model="customAspects.{{ $aspectIndex }}.title"
                            :label="__('page.grant-planning-assessment.label-aspect-title')"
                            type="text"
                            class="flex-1"
                        />
                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeCustomAspect({{ $aspectIndex }})" class="ml-2 mt-6" />
                    </div>

                    @foreach ($customAspect['paragraphs'] as $paragraphIndex => $paragraph)
                        <div class="space-y-1">
                            <flux:editor
                                wire:model="customAspects.{{ $aspectIndex }}.paragraphs.{{ $paragraphIndex }}"
                                toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                            />
                            <flux:error name="customAspects.{{ $aspectIndex }}.paragraphs.{{ $paragraphIndex }}" />
                            @if (count($customAspect['paragraphs']) > 1)
                                <div class="flex justify-end">
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeCustomParagraph({{ $aspectIndex }}, {{ $paragraphIndex }})" />
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <flux:button variant="ghost" size="sm" icon="plus" wire:click="addCustomParagraph({{ $aspectIndex }})">
                        {{ __('page.grant-planning-assessment.add-paragraph') }}
                    </flux:button>
                </div>
            @endforeach

            <flux:button variant="ghost" icon="plus" wire:click="addCustomAspect">
                {{ __('page.grant-planning-assessment.add-custom-aspect') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-agreement.step-layout>
