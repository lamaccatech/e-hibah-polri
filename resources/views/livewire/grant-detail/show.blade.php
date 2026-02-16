<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('page.grant-detail.title') }}</flux:heading>
        <flux:text class="mt-1">{{ $grant->nama_hibah }}</flux:text>
    </div>

    <flux:navbar class="mb-6">
        <flux:navbar.item
            wire:click.prevent="switchTab('grant-info')"
            :current="$activeTab === 'grant-info'"
            class="cursor-pointer"
        >
            {{ __('page.grant-detail.tab-grant-info') }}
        </flux:navbar.item>
        <flux:navbar.item
            wire:click.prevent="switchTab('proposal-info')"
            :current="$activeTab === 'proposal-info'"
            class="cursor-pointer"
        >
            {{ __('page.grant-detail.tab-proposal-info') }}
        </flux:navbar.item>
        <flux:navbar.item
            wire:click.prevent="switchTab('assessment-info')"
            :current="$activeTab === 'assessment-info'"
            class="cursor-pointer"
        >
            {{ __('page.grant-detail.tab-assessment-info') }}
        </flux:navbar.item>
    </flux:navbar>

    @if ($activeTab === 'grant-info')
        @include('livewire.grant-detail._tab-grant-info')
    @elseif ($activeTab === 'proposal-info')
        @include('livewire.grant-detail._tab-proposal-info')
    @elseif ($activeTab === 'assessment-info')
        @include('livewire.grant-detail._tab-assessment-info')
    @endif
</div>
