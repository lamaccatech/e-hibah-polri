<x-grant-agreement.step-layout :grant="$grant" :currentStep="7">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-draft.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-8">
        <div class="space-y-2">
            <flux:file-upload wire:model="draftFile" :label="__('page.grant-agreement-draft.label-file')" accept=".pdf">
                <flux:file-upload.dropzone
                    :heading="__('page.grant-agreement-draft.label-file')"
                    :text="__('page.grant-agreement-draft.hint-file')"
                />
            </flux:file-upload>
            <flux:error name="draftFile" />

            @if ($draftFile)
                <flux:file-item :heading="$draftFile->getClientOriginalName()" :size="rescue(fn () => $draftFile->getSize())" />
            @endif
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-agreement.step-layout>
