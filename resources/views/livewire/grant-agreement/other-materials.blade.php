<x-grant-agreement.step-layout :grant="$grant" :currentStep="6">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-other.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-8">
        @foreach ($customChapters as $chapterIndex => $customChapter)
            <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" wire:key="chapter-{{ $chapterIndex }}">
                <div class="flex items-center justify-between">
                    <flux:input
                        wire:model="customChapters.{{ $chapterIndex }}.title"
                        :label="__('page.grant-planning-proposal.label-chapter-title')"
                        type="text"
                        class="flex-1"
                    />
                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeCustomChapter({{ $chapterIndex }})" class="ml-2 mt-6" />
                </div>

                @foreach ($customChapter['paragraphs'] as $paragraphIndex => $paragraph)
                    <div class="space-y-1" wire:key="chapter-{{ $chapterIndex }}-p-{{ $paragraphIndex }}">
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

        <div class="flex items-center gap-4">
            @if (count($customChapters) > 0)
                <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            @endif
            <flux:button variant="ghost" wire:click="skip">{{ __('page.grant-agreement-other.skip') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-agreement.step-layout>
