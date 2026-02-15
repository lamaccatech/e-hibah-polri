<x-grant-planning.step-layout :currentStep="1">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-planning-create.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-lg">
        <flux:input
            wire:model="activityName"
            :label="__('page.grant-planning-create.label-activity-name')"
            :placeholder="__('page.grant-planning-create.placeholder-activity-name')"
            type="text"
            required
        />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.save') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-planning.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-planning.step-layout>
