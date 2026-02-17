@props([
    'grant' => null,
    'currentStep' => 1,
])

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:sticky md:top-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('page.grant-agreement.title') }}">
            <flux:navlist.item
                :href="$grant ? route('grant-agreement.reception-basis', $grant) : route('grant-agreement.create')"
                :current="$currentStep === 1"
                :icon="$currentStep > 1 ? 'check-circle' : 'pencil-square'"
                wire:navigate
            >
                {{ __('component.grant-agreement-steps.step-1') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="$grant ? route('grant-agreement.donor', $grant) : '#'"
                :current="$currentStep === 2"
                :icon="$currentStep > 2 ? 'check-circle' : ($currentStep === 2 ? 'pencil-square' : 'ellipsis-horizontal-circle')"
                wire:navigate
            >
                {{ __('component.grant-agreement-steps.step-2') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="$grant ? route('grant-agreement.assessment', $grant) : '#'"
                :current="$currentStep === 3"
                :icon="$currentStep > 3 ? 'check-circle' : ($currentStep === 3 ? 'pencil-square' : 'ellipsis-horizontal-circle')"
                wire:navigate
            >
                {{ __('component.grant-agreement-steps.step-3') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="$grant ? route('grant-agreement.harmonization', $grant) : '#'"
                :current="$currentStep === 4"
                :icon="$currentStep > 4 ? 'check-circle' : ($currentStep === 4 ? 'pencil-square' : 'ellipsis-horizontal-circle')"
                wire:navigate
            >
                {{ __('component.grant-agreement-steps.step-4') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="$grant ? route('grant-agreement.additional', $grant) : '#'"
                :current="$currentStep === 5"
                :icon="$currentStep > 5 ? 'check-circle' : ($currentStep === 5 ? 'pencil-square' : 'ellipsis-horizontal-circle')"
                wire:navigate
            >
                {{ __('component.grant-agreement-steps.step-5') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="$grant ? route('grant-agreement.other', $grant) : '#'"
                :current="$currentStep === 6"
                :icon="$currentStep > 6 ? 'check-circle' : ($currentStep === 6 ? 'pencil-square' : 'ellipsis-horizontal-circle')"
                wire:navigate
            >
                {{ __('component.grant-agreement-steps.step-6') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="$grant ? route('grant-agreement.draft', $grant) : '#'"
                :current="$currentStep === 7"
                :icon="$currentStep > 7 ? 'check-circle' : ($currentStep === 7 ? 'pencil-square' : 'ellipsis-horizontal-circle')"
                wire:navigate
            >
                {{ __('component.grant-agreement-steps.step-7') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        {{ $slot }}
    </div>
</div>
