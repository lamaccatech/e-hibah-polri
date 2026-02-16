<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ $documentType->label() }}</flux:heading>
        <flux:text class="mt-1">{{ $grant->nama_hibah }}</flux:text>
    </div>

    @unless ($chief)
        <flux:callout variant="warning">
            <flux:callout.heading>{{ __('page.grant-document.no-chief-title') }}</flux:callout.heading>
            <flux:callout.text>{{ __('page.grant-document.no-chief-description') }}</flux:callout.text>
        </flux:callout>
    @else
        <div class="max-w-sm space-y-4">
            <flux:field>
                <flux:label>{{ __('page.grant-document.label-date') }}</flux:label>
                <flux:input type="date" wire:model="documentDate" />
                <flux:error name="documentDate" />
            </flux:field>

            <div class="flex gap-2">
                <flux:button wire:click="preview" variant="ghost">
                    {{ $showPreview ? __('page.grant-document.hide-preview') : __('page.grant-document.show-preview') }}
                </flux:button>
                <flux:button wire:click="download" variant="primary" icon="arrow-down-tray">
                    {{ __('page.grant-document.download-pdf') }}
                </flux:button>
            </div>
        </div>

        @if ($showPreview && $previewData)
            <div class="mt-8 border rounded-lg bg-white p-8 shadow-sm max-w-4xl mx-auto">
                @include($previewView, $previewData)
            </div>
        @endif
    @endunless
</div>
