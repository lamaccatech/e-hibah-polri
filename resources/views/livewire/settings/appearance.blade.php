<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('page.appearance.sr-title') }}</flux:heading>

    <x-settings.layout :heading="__('page.appearance.title')" :subheading=" __('page.appearance.description')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('page.appearance.light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('page.appearance.dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('page.appearance.system') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
