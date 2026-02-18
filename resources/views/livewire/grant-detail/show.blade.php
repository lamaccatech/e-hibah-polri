<div>
    <div class="mb-6 flex items-start justify-between">
        <div>
            <flux:heading size="xl">{{ __('page.grant-detail.title') }}</flux:heading>
            <flux:text class="mt-1">{{ $grant->nama_hibah }}</flux:text>
        </div>
        <div class="flex items-center gap-2">
            @if ($isAgreementStage && $hasProposal && auth()->user()->unit->level_unit === \App\Enums\UnitLevel::SatuanKerja)
                <flux:button variant="ghost" size="sm" wire:click="toggleShowProposal">
                    {{ $showProposal ? __('page.grant-detail.hide-proposal-button') : __('page.grant-detail.show-proposal-button') }}
                </flux:button>
            @endif
            @if ($canUploadSigned)
                <flux:button variant="primary" size="sm" icon="arrow-up-tray" :href="route('grant-agreement.upload-signed', $grant)" wire:navigate>
                    {{ __('page.grant-agreement.upload-signed-button') }}
                </flux:button>
            @endif
            @if ($canSubmitSehati)
                <flux:button variant="primary" size="sm" :href="route('grant-agreement.sehati', $grant)" wire:navigate>
                    {{ __('page.grant-agreement.sehati-button') }}
                </flux:button>
            @endif
            @if ((!$isAgreementStage || $showProposal) && auth()->user()->unit->level_unit === \App\Enums\UnitLevel::SatuanKerja && !$grant->statusHistory->last()?->status_sesudah?->isRejected())
                <flux:dropdown>
                    <flux:button variant="primary" size="sm" icon="document-text" icon-trailing="chevron-down">
                        {{ __('page.grant-detail.generate-document') }}
                    </flux:button>
                    <flux:menu>
                        @foreach (\App\Enums\GrantGeneratedDocumentType::cases() as $docType)
                            <flux:menu.item :href="route('grant-document.generate', [$grant, $docType->slug()])" wire:navigate>
                                {{ $docType->label() }}
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            @endif
        </div>
    </div>

    <div class="mb-6">
        <flux:navbar>
            {{-- Always visible: Informasi Hibah --}}
            <flux:navbar.item
                wire:click.prevent="switchTab('grant-info')"
                :current="$activeTab === 'grant-info'"
                class="cursor-pointer"
            >
                {{ __('page.grant-detail.tab-grant-info') }}
            </flux:navbar.item>

            @if ($isAgreementStage)
                {{-- Agreement tabs --}}
                <flux:navbar.item
                    wire:click.prevent="switchTab('agreement-info')"
                    :current="$activeTab === 'agreement-info'"
                    class="cursor-pointer"
                >
                    {{ __('page.grant-detail.tab-agreement-info') }}
                </flux:navbar.item>
                <flux:navbar.item
                    wire:click.prevent="switchTab('agreement-assessment')"
                    :current="$activeTab === 'agreement-assessment'"
                    class="cursor-pointer"
                >
                    {{ __('page.grant-detail.tab-agreement-assessment') }}
                </flux:navbar.item>

                {{-- Planning tabs shown when toggle is active --}}
                @if ($showProposal)
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
                    <flux:navbar.item
                        wire:click.prevent="switchTab('document-history')"
                        :current="$activeTab === 'document-history'"
                        class="cursor-pointer"
                    >
                        {{ __('page.grant-detail.tab-document-history') }}
                    </flux:navbar.item>
                @endif
            @else
                {{-- Planning tabs --}}
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
                <flux:navbar.item
                    wire:click.prevent="switchTab('document-history')"
                    :current="$activeTab === 'document-history'"
                    class="cursor-pointer"
                >
                    {{ __('page.grant-detail.tab-document-history') }}
                </flux:navbar.item>
            @endif

            {{-- Always visible: Riwayat Perubahan --}}
            <flux:navbar.item
                wire:click.prevent="switchTab('change-history')"
                :current="$activeTab === 'change-history'"
                class="cursor-pointer"
            >
                {{ __('page.grant-detail-change-history.tab-label') }}
            </flux:navbar.item>
        </flux:navbar>
    </div>

    @if ($activeTab === 'grant-info')
        @include('livewire.grant-detail._tab-grant-info')
    @elseif ($activeTab === 'proposal-info')
        @include('livewire.grant-detail._tab-proposal-info')
    @elseif ($activeTab === 'assessment-info')
        @include('livewire.grant-detail._tab-assessment-info')
    @elseif ($activeTab === 'agreement-info')
        @include('livewire.grant-detail._tab-agreement-info')
    @elseif ($activeTab === 'agreement-assessment')
        @include('livewire.grant-detail._tab-agreement-assessment-info')
    @elseif ($activeTab === 'document-history')
        @include('livewire.grant-detail._tab-document-history')
    @elseif ($activeTab === 'change-history')
        @include('livewire.grant-detail._tab-change-history')
    @endif
</div>
