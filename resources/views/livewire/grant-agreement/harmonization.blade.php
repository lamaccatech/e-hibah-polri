<x-grant-agreement.step-layout :grant="$grant" :currentStep="4">
    <flux:heading size="xl" class="mb-6">{{ __('page.grant-agreement-harmonization.title') }}</flux:heading>

    <form wire:submit="save" class="max-w-3xl space-y-8">
        {{-- 4.1 Bentuk Hibah --}}
        <div class="space-y-2">
            <flux:heading size="lg">{{ __('page.grant-agreement-harmonization.label-grant-forms') }}</flux:heading>
            <flux:checkbox.group wire:model="grantForms">
                @foreach ($grantFormOptions as $option)
                    <flux:checkbox :value="$option" :label="$option" />
                @endforeach
            </flux:checkbox.group>
            <flux:error name="grantForms" />
        </div>

        <flux:separator />

        {{-- 4.2 Rencana Anggaran Kebutuhan --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('page.grant-agreement-harmonization.section-budget') }}</flux:heading>

            <flux:select
                variant="combobox"
                wire:model="currency"
                :label="__('page.grant-planning-proposal.label-currency')"
                :placeholder="__('page.grant-planning-proposal.label-currency')"
            >
                @foreach ($currencyOptions as $currencyOption)
                    <flux:select.option :value="$currencyOption">{{ $currencyOption }}</flux:select.option>
                @endforeach
            </flux:select>

            @foreach ($budgetItems as $index => $item)
                <div class="flex items-start gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="budget-{{ $index }}">
                    <div class="flex-1 space-y-3">
                        <flux:input
                            wire:model="budgetItems.{{ $index }}.uraian"
                            :label="__('page.grant-planning-proposal.label-description')"
                            type="text"
                        />
                        <flux:input
                            :label="__('page.grant-planning-proposal.label-value')"
                            type="text"
                            inputmode="numeric"
                            value="{{ $item['nilai'] ? number_format((float) $item['nilai'], 0, ',', '.') : '' }}"
                            x-on:input="
                                let raw = $event.target.value.replace(/\D/g, '');
                                $event.target.value = raw ? new Intl.NumberFormat('id-ID').format(Number(raw)) : '';
                                $wire.set('budgetItems.{{ $index }}.nilai', raw);
                            "
                        />
                    </div>
                    @if (count($budgetItems) > 1)
                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeBudgetItem({{ $index }})" class="mt-6" />
                    @endif
                </div>
            @endforeach

            <flux:button variant="ghost" size="sm" icon="plus" wire:click="addBudgetItem">
                {{ __('page.grant-planning-proposal.add-budget-item') }}
            </flux:button>
        </div>

        <flux:separator />

        {{-- 4.3 Rencana Penarikan Hibah --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('page.grant-agreement-harmonization.section-withdrawal') }}</flux:heading>

            @foreach ($withdrawalPlans as $index => $plan)
                <div class="flex items-start gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="withdrawal-{{ $index }}">
                    <div class="flex-1 space-y-3">
                        <flux:input
                            wire:model="withdrawalPlans.{{ $index }}.uraian"
                            :label="__('page.grant-planning-proposal.label-description')"
                            type="text"
                        />
                        <flux:date-picker
                            wire:model="withdrawalPlans.{{ $index }}.tanggal"
                            :label="__('page.grant-agreement-harmonization.label-withdrawal-date')"
                        />
                        <flux:input
                            :label="__('page.grant-planning-proposal.label-value')"
                            type="text"
                            inputmode="numeric"
                            value="{{ $plan['nilai'] ? number_format((float) $plan['nilai'], 0, ',', '.') : '' }}"
                            x-on:input="
                                let raw = $event.target.value.replace(/\D/g, '');
                                $event.target.value = raw ? new Intl.NumberFormat('id-ID').format(Number(raw)) : '';
                                $wire.set('withdrawalPlans.{{ $index }}.nilai', raw);
                            "
                        />
                    </div>
                    @if (count($withdrawalPlans) > 1)
                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeWithdrawalPlan({{ $index }})" class="mt-6" />
                    @endif
                </div>
            @endforeach

            <flux:button variant="ghost" size="sm" icon="plus" wire:click="addWithdrawalPlan">
                {{ __('page.grant-agreement-harmonization.add-withdrawal') }}
            </flux:button>
            <flux:error name="withdrawalPlans" />
        </div>

        <flux:separator />

        {{-- 4.4 Mekanisme Pengawasan Hibah --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('page.grant-agreement-harmonization.section-supervision') }}</flux:heading>

            @foreach ($supervisionParagraphs as $index => $paragraph)
                <div class="space-y-1" wire:key="supervision-{{ $index }}">
                    <flux:editor
                        wire:model="supervisionParagraphs.{{ $index }}"
                        toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                    />
                    <flux:error name="supervisionParagraphs.{{ $index }}" />
                    @if (count($supervisionParagraphs) > 1)
                        <div class="flex justify-end">
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeSupervisionParagraph({{ $index }})" />
                        </div>
                    @endif
                </div>
            @endforeach

            <flux:button variant="ghost" size="sm" icon="plus" wire:click="addSupervisionParagraph">
                {{ __('page.grant-planning-assessment.add-paragraph') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('common.continue') }}</flux:button>
            <flux:button variant="ghost" :href="route('grant-agreement.index')" wire:navigate>{{ __('common.cancel') }}</flux:button>
        </div>
    </form>
</x-grant-agreement.step-layout>
