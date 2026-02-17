<div class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    {{-- Section 1: Planning Stats --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('page.dashboard.mabes-planning-heading') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-blue-50 p-2.5 dark:bg-blue-900/30">
                    <flux:icon.document-text class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-planning-created') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningCreated'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-amber-50 p-2.5 dark:bg-amber-900/30">
                    <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-planning-unprocessed') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningUnprocessed'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-indigo-50 p-2.5 dark:bg-indigo-900/30">
                    <flux:icon.arrow-path class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-planning-processing') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningProcessing'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-red-50 p-2.5 dark:bg-red-900/30">
                    <flux:icon.x-circle class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-planning-rejected') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['planningRejected'] }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 2: Agreement Stats --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('page.dashboard.mabes-agreement-heading') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-blue-50 p-2.5 dark:bg-blue-900/30">
                    <flux:icon.document-check class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-agreement-created') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementCreated'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-amber-50 p-2.5 dark:bg-amber-900/30">
                    <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-agreement-unprocessed') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementUnprocessed'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-indigo-50 p-2.5 dark:bg-indigo-900/30">
                    <flux:icon.arrow-path class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-agreement-processing') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementProcessing'] }}</flux:heading>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="rounded-lg bg-red-50 p-2.5 dark:bg-red-900/30">
                    <flux:icon.x-circle class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:text class="text-sm">{{ __('page.dashboard.mabes-agreement-rejected') }}</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $counts['agreementRejected'] }}</flux:heading>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3: Realization --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('page.dashboard.mabes-realization-heading') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2">
            {{-- Goods/Services --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium">{{ __('page.dashboard.mabes-realization-goods') }}</flux:text>
                @php
                    $goodsPlan = $realization['goodsServices']['plan'];
                    $goodsReal = $realization['goodsServices']['realization'];
                    $goodsPercent = $goodsPlan > 0 ? round(($goodsReal / $goodsPlan) * 100, 1) : 0;
                @endphp
                <div class="mt-4 space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-plan') }}</flux:text>
                            <flux:text class="text-xs font-medium">Rp {{ number_format($goodsPlan, 0, ',', '.') }}</flux:text>
                        </div>
                        <div class="mt-1 h-2.5 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div class="h-2.5 rounded-full bg-blue-500" style="width: 100%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-actual') }}</flux:text>
                            <flux:text class="text-xs font-medium">Rp {{ number_format($goodsReal, 0, ',', '.') }}</flux:text>
                        </div>
                        <div class="mt-1 h-2.5 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ min($goodsPercent, 100) }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <flux:badge size="sm" :color="$goodsPercent >= 100 ? 'green' : ($goodsPercent >= 50 ? 'amber' : 'zinc')">
                        {{ $goodsPercent }}%
                    </flux:badge>
                    <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-percentage') }}</flux:text>
                </div>
            </div>

            {{-- Money --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium">{{ __('page.dashboard.mabes-realization-money') }}</flux:text>
                @php
                    $moneyPlan = $realization['money']['plan'];
                    $moneyReal = $realization['money']['realization'];
                    $moneyPercent = $moneyPlan > 0 ? round(($moneyReal / $moneyPlan) * 100, 1) : 0;
                @endphp
                <div class="mt-4 space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-plan') }}</flux:text>
                            <flux:text class="text-xs font-medium">Rp {{ number_format($moneyPlan, 0, ',', '.') }}</flux:text>
                        </div>
                        <div class="mt-1 h-2.5 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div class="h-2.5 rounded-full bg-blue-500" style="width: 100%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-actual') }}</flux:text>
                            <flux:text class="text-xs font-medium">Rp {{ number_format($moneyReal, 0, ',', '.') }}</flux:text>
                        </div>
                        <div class="mt-1 h-2.5 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ min($moneyPercent, 100) }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <flux:badge size="sm" :color="$moneyPercent >= 100 ? 'green' : ($moneyPercent >= 50 ? 'amber' : 'zinc')">
                        {{ $moneyPercent }}%
                    </flux:badge>
                    <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-percentage') }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 4: Yearly Trend --}}
    @if (count($yearlyTrend) > 0)
        <div>
            <flux:heading size="lg" class="mb-4">{{ __('page.dashboard.mabes-trend-heading') }}</flux:heading>
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <div class="mb-4 flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="size-2.5 rounded-full bg-blue-500"></div>
                        <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-plan') }}</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="size-2.5 rounded-full bg-emerald-500"></div>
                        <flux:text class="text-xs">{{ __('page.dashboard.mabes-realization-actual') }}</flux:text>
                    </div>
                </div>

                <flux:chart :value="$yearlyTrend" class="aspect-3/1">
                    <flux:chart.svg>
                        <flux:chart.area field="plan" class="text-blue-500/10" />
                        <flux:chart.line field="plan" class="text-blue-500" />
                        <flux:chart.area field="realization" class="text-emerald-500/10" />
                        <flux:chart.line field="realization" class="text-emerald-500" />

                        <flux:chart.axis axis="x" field="year">
                            <flux:chart.axis.line />
                            <flux:chart.axis.tick />
                        </flux:chart.axis>

                        <flux:chart.axis axis="y">
                            <flux:chart.axis.grid />
                            <flux:chart.axis.tick />
                        </flux:chart.axis>

                        <flux:chart.cursor />
                    </flux:chart.svg>

                    <flux:chart.tooltip>
                        <flux:chart.tooltip.heading field="year" />
                        <flux:chart.tooltip.value field="plan" label="{{ __('page.dashboard.mabes-realization-plan') }}">
                            <div class="size-2.5 rounded-full bg-blue-500"></div>
                        </flux:chart.tooltip.value>
                        <flux:chart.tooltip.value field="realization" label="{{ __('page.dashboard.mabes-realization-actual') }}">
                            <div class="size-2.5 rounded-full bg-emerald-500"></div>
                        </flux:chart.tooltip.value>
                    </flux:chart.tooltip>
                </flux:chart>
            </div>
        </div>
    @endif
</div>
