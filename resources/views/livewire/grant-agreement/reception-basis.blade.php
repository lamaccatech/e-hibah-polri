<x-grant-agreement.step-layout :grant="$grant" :currentStep="1">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-reception.title') }}</flux:heading>

    <form wire:submit="save" class="space-y-8 max-w-3xl">
        {{-- Nomor Surat --}}
        <flux:field>
            <flux:label>{{ __('page.grant-agreement-reception.label-letter-number') }}</flux:label>
            <flux:input
                wire:model.live.debounce.500ms="letterNumber"
                type="text"
                required
            />
            <flux:error name="letterNumber" />
            @if ($hasProposal && !$grant)
                <flux:text class="text-green-600 dark:text-green-400 text-sm">
                    {{ __('page.grant-agreement-reception.linked-to-planning') }}
                </flux:text>
            @endif
        </flux:field>

        {{-- Nama Kegiatan --}}
        <flux:field>
            <flux:label>{{ __('page.grant-agreement-reception.label-activity-name') }}</flux:label>
            <flux:input
                wire:model="activityName"
                type="text"
                class:input="uppercase"
                x-on:input="$event.target.value = $event.target.value.toUpperCase()"
                required
            />
            <flux:error name="activityName" />
        </flux:field>

        {{-- Surat dari Pemberi Hibah (only for direct grants, not editing) --}}
        @if (!$hasProposal && !$grant)
            <div class="space-y-2">
                <flux:file-upload wire:model="donorLetter" :label="__('page.grant-agreement-reception.label-donor-letter')" accept=".pdf,.jpg,.png">
                    <flux:file-upload.dropzone
                        :heading="__('page.grant-agreement-reception.label-donor-letter')"
                        :text="__('page.grant-agreement-reception.donor-letter-hint')"
                    />
                </flux:file-upload>
                <flux:error name="donorLetter" />

                @if ($donorLetter)
                    <flux:file-item :heading="$donorLetter->getClientOriginalName()" :size="rescue(fn () => $donorLetter->getSize())" />
                @endif
            </div>
        @endif

        {{-- Tujuan --}}
        <div class="space-y-3">
            <flux:heading size="lg">{{ \App\Enums\ProposalChapter::Objective->label() }}</flux:heading>

            @foreach ($objectives as $index => $objective)
                <div wire:key="objective-{{ $index }}" class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">#{{ $index + 1 }}</span>
                        @if (count($objectives) > 1)
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeObjective({{ $index }})" />
                        @endif
                    </div>

                    <flux:select
                        variant="combobox"
                        wire:model="objectives.{{ $index }}.purpose"
                        :placeholder="__('page.grant-planning-proposal.placeholder-purpose')"
                    >
                        @foreach ($purposeOptions as $purpose)
                            <flux:select.option :value="$purpose">{{ $purpose }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="objectives.{{ $index }}.purpose" />

                    <flux:editor
                        wire:model="objectives.{{ $index }}.detail"
                        toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                    />
                    <flux:error name="objectives.{{ $index }}.detail" />
                </div>
            @endforeach

            <flux:button variant="ghost" icon="plus" wire:click="addObjective">
                {{ __('page.grant-planning-proposal.add-objective') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-agreement.step-layout>
