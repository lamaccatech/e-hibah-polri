<x-grant-agreement.step-layout :grant="$grant" :currentStep="7">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-draft.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-8">
        <div class="space-y-2">
            <flux:input
                wire:model="draftFile"
                :label="__('page.grant-agreement-draft.label-file')"
                type="file"
                accept=".pdf"
            />
            <flux:text class="text-sm text-zinc-500">{{ __('page.grant-agreement-draft.hint-file') }}</flux:text>
            <flux:error name="draftFile" />
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-agreement.step-layout>
