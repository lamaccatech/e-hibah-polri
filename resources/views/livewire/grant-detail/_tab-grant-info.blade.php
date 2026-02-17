<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Grant overview + Donor --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Grant Overview --}}
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <flux:heading size="lg" class="mb-4">{{ __('page.grant-detail.grant-overview') }}</flux:heading>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-activity-name') }}</dt>
                    <dd class="mt-1 font-medium">{{ $grant->nama_hibah }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-satker') }}</dt>
                    <dd class="mt-1 font-medium">{{ $grant->orgUnit?->nama_unit ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-polda') }}</dt>
                    <dd class="mt-1 font-medium">{{ $grant->orgUnit?->parent?->nama_unit ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-type') }}</dt>
                    <dd class="mt-1 font-medium">{{ $grant->jenis_hibah?->value ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-value') }}</dt>
                    <dd class="mt-1 font-medium">
                        @if ($grant->nilai_hibah)
                            {{ $grant->mata_uang }} {{ number_format($grant->nilai_hibah, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-status') }}</dt>
                    <dd class="mt-1">
                        <flux:badge size="sm">
                            {{ $grant->statusHistory->last()?->status_sesudah?->label() ?? '-' }}
                        </flux:badge>
                    </dd>
                </div>
                @php $planningNumber = $grant->numberings->where('tahapan', \App\Enums\GrantStage::Planning)->first()?->nomor; @endphp
                @if ($planningNumber)
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-planning-number') }}</dt>
                        <dd class="mt-1">
                            <span
                                class="inline-flex items-center gap-1.5 font-mono font-medium cursor-pointer"
                                x-data="{ copied: false }"
                                x-on:click="
                                    navigator.clipboard.writeText('{{ $planningNumber }}');
                                    copied = true;
                                    setTimeout(() => copied = false, 1500);
                                "
                            >
                                {{ $planningNumber }}
                                <template x-if="!copied"><x-flux::icon.clipboard-document class="size-4 text-zinc-400" /></template>
                                <template x-if="copied"><x-flux::icon.check class="size-4 text-green-500" /></template>
                            </span>
                        </dd>
                    </div>
                @endif
                @php $agreementNumber = $grant->numberings->where('tahapan', \App\Enums\GrantStage::Agreement)->first()?->nomor; @endphp
                @if ($agreementNumber)
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-agreement-number') }}</dt>
                        <dd class="mt-1 flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 font-mono font-medium cursor-pointer"
                                x-data="{ copied: false }"
                                x-on:click="
                                    navigator.clipboard.writeText('{{ $agreementNumber }}');
                                    copied = true;
                                    setTimeout(() => copied = false, 1500);
                                "
                            >
                                {{ $agreementNumber }}
                                <template x-if="!copied"><x-flux::icon.clipboard-document class="size-4 text-zinc-400" /></template>
                                <template x-if="copied"><x-flux::icon.check class="size-4 text-green-500" /></template>
                            </span>
                            @if ($canReviseAgreementNumber)
                                <flux:button
                                    size="sm"
                                    variant="subtle"
                                    icon="arrow-path"
                                    wire:click="reviseAgreementNumberMonth"
                                    wire:confirm="{{ __('page.grant-detail.revise-number-confirm') }}"
                                >
                                    {{ __('page.grant-detail.revise-number-button') }}
                                </flux:button>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Uploaded Documents --}}
        @if ($uploadedFiles->isNotEmpty())
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <flux:heading size="lg" class="mb-4">{{ __('page.grant-detail.uploaded-documents') }}</flux:heading>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($uploadedFiles as $file)
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $file->name }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $file->file_type->label() }} &middot; {{ $file->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                            <a href="{{ route('grant-file.download', [$grant, $file]) }}"
                               class="ml-4 shrink-0">
                                <flux:button size="sm" variant="subtle" icon="arrow-down-tray" />
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- SEHATI Data --}}
        @if ($grant->financeMinistrySubmission)
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <flux:heading size="lg" class="mb-4">{{ __('page.grant-detail.sehati-info') }}</flux:heading>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-sehati-grant-recipient') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->financeMinistrySubmission->penerima_hibah }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-sehati-funding-source') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->financeMinistrySubmission->sumber_pembiayaan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-sehati-funding-type') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->financeMinistrySubmission->jenis_pembiayaan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-sehati-withdrawal-method') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->financeMinistrySubmission->cara_penarikan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-sehati-effective-date') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->financeMinistrySubmission->tanggal_efektif->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-sehati-withdrawal-deadline') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->financeMinistrySubmission->tanggal_batas_penarikan->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-sehati-account-closing-date') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->financeMinistrySubmission->tanggal_penutupan_rekening->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>
        @endif

        {{-- Donor Info --}}
        @if ($grant->donor)
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <flux:heading size="lg" class="mb-4">{{ __('page.grant-detail.donor-info') }}</flux:heading>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-donor-name') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->donor->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-donor-origin') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->donor->asal ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-donor-category') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->donor->kategori ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-donor-country') }}</dt>
                        <dd class="mt-1 font-medium">{{ $grant->donor->negara ?? '-' }}</dd>
                    </div>
                    @if ($grant->donor->alamat)
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('page.grant-detail.label-donor-address') }}</dt>
                            <dd class="mt-1 font-medium">{{ $grant->donor->alamat }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
    </div>

    {{-- Right: Status Timeline --}}
    <div class="lg:col-span-1">
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <flux:heading size="lg" class="mb-4">{{ __('page.grant-detail.status-timeline') }}</flux:heading>

            @if ($statusHistory->isNotEmpty())
                <div class="relative space-y-6">
                    <div class="absolute top-2 bottom-2 left-[7px] w-px bg-zinc-200 dark:bg-zinc-700"></div>

                    @foreach ($statusHistory as $history)
                        <div class="relative flex gap-3">
                            <div class="mt-1.5 size-[15px] shrink-0 rounded-full border-2 border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 z-10"></div>
                            <div class="min-w-0">
                                <flux:badge size="sm">{{ $history->status_sesudah?->label() ?? '-' }}</flux:badge>
                                @if ($history->keterangan)
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $history->keterangan }}</p>
                                @endif
                                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $history->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text>{{ __('page.grant-detail.no-status-history') }}</flux:text>
            @endif
        </div>
    </div>
</div>
