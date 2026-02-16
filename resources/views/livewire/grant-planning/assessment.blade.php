<x-grant-planning.step-layout :grant="$grant" :currentStep="4">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-planning-assessment.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-8 max-w-3xl">
        {{-- Mandatory Aspects --}}
        <div class="space-y-8">
            @foreach ($aspectCases as $aspect)
                <div class="space-y-3 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <flux:heading size="xl" class="font-bold">{{ $aspect->label() }}</flux:heading>
                        @if (isset($reviewFeedback[$aspect->value]))
                            @php $feedback = $reviewFeedback[$aspect->value]; @endphp
                            @if ($feedback['result'] === \App\Enums\AssessmentResult::Fulfilled->value)
                                <flux:badge color="green" size="sm">
                                    <flux:icon.check-circle variant="mini" class="size-3.5" />
                                    {{ __('page.grant-planning-assessment.feedback-fulfilled') }}
                                </flux:badge>
                            @elseif ($feedback['result'] === \App\Enums\AssessmentResult::Revision->value)
                                <flux:badge color="yellow" size="sm">
                                    <flux:icon.exclamation-circle variant="mini" class="size-3.5" />
                                    {{ __('page.grant-planning-assessment.feedback-revision') }}
                                </flux:badge>
                            @endif
                        @endif
                    </div>
                    @if (isset($reviewFeedback[$aspect->value]) && $reviewFeedback[$aspect->value]['result'] === \App\Enums\AssessmentResult::Revision->value && $reviewFeedback[$aspect->value]['remarks'])
                        <div class="text-sm text-amber-700 dark:text-amber-300 prose prose-sm dark:prose-invert max-w-none">
                            {!! $reviewFeedback[$aspect->value]['remarks'] !!}
                        </div>
                    @endif

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
                <div class="p-4 border rounded-lg border-zinc-200 dark:border-zinc-700 space-y-3">
                    <div class="flex justify-between items-center">
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
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-planning.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-planning.step-layout>
