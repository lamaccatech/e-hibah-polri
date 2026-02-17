<x-grant-agreement.step-layout :grant="$grant" :currentStep="5">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-additional.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-8">
        @foreach ($chapterCases as $chapter)
            <div class="space-y-4">
                <flux:heading size="lg">{{ $chapter->label() }}</flux:heading>

                @foreach ($chapter->prompts() as $promptIndex => $prompt)
                    <div class="space-y-1">
                        <flux:text class="text-sm font-medium">{{ $prompt }}</flux:text>
                        <flux:editor
                            wire:model="chapters.{{ $chapter->value }}.{{ $promptIndex }}"
                            toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                        />
                        <flux:error name="chapters.{{ $chapter->value }}.{{ $promptIndex }}" />
                    </div>
                @endforeach
            </div>

            @if (! $loop->last)
                <flux:separator />
            @endif
        @endforeach

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-agreement.step-layout>
