<div>
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-upload-signed.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-8">
        <div class="space-y-2">
            <flux:file-upload wire:model="signedAgreementFile" :label="__('page.grant-agreement-upload-signed.label-file')" accept=".pdf">
                <flux:file-upload.dropzone
                    :heading="__('page.grant-agreement-upload-signed.label-file')"
                    :text="__('page.grant-agreement-upload-signed.hint-file')"
                />
            </flux:file-upload>
            <flux:error name="signedAgreementFile" />

            @if ($signedAgreementFile)
                <flux:file-item :heading="$signedAgreementFile->getClientOriginalName()" :size="rescue(fn () => $signedAgreementFile->getSize())" />
            @endif
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-detail.show', $grant)" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</div>
