@php
    $aspects = \App\Enums\AssessmentAspect::cases();
    $hasAnyData = !empty($satkerAssessments) || !empty($poldaResults) || !empty($mabesResults);
@endphp

<div class="max-w-3xl space-y-8">
    @if ($hasAnyData)
        @foreach ($aspects as $aspect)
            @php
                $satkerContents = $satkerAssessments[$aspect->value] ?? null;
                $poldaResult = $poldaResults[$aspect->value] ?? null;
                $mabesResult = $mabesResults[$aspect->value] ?? null;
                $hasData = $satkerContents || $poldaResult || $mabesResult;
            @endphp

            @if ($hasData)
                <div>
                    <flux:heading size="xl">{{ $aspect->label() }}</flux:heading>

                    {{-- Satker Assessment --}}
                    @if ($satkerContents && $satkerContents->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            <p class="font-semibold text-base text-zinc-700 dark:text-zinc-300">{{ __('page.grant-detail.satker-assessment') }}</p>
                            @foreach ($satkerContents as $content)
                                @if ($content->subjudul)
                                    <p class="font-medium text-sm text-zinc-700 dark:text-zinc-300">{{ $content->subjudul }}</p>
                                @endif
                                <div class="text-sm text-zinc-600 dark:text-zinc-400 prose prose-sm dark:prose-invert max-w-none">{!! $content->isi !!}</div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Polda Result --}}
                    @if ($poldaResult)
                        <div class="mt-4">
                            <p class="font-semibold text-base text-zinc-700 dark:text-zinc-300 mb-2">{{ __('page.grant-detail.polda-result') }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @php
                                        $poldaBadgeColor = match ($poldaResult->rekomendasi) {
                                            \App\Enums\AssessmentResult::Fulfilled => 'green',
                                            \App\Enums\AssessmentResult::Revision => 'yellow',
                                            \App\Enums\AssessmentResult::Rejected => 'red',
                                        };
                                    @endphp
                                    <flux:badge size="sm" :color="$poldaBadgeColor">{{ $poldaResult->rekomendasi->label() }}</flux:badge>
                                    @if ($poldaResult->orgUnit)
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $poldaResult->orgUnit->nama_unit }}</span>
                                    @endif
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $poldaResult->created_at->format('d M Y H:i') }}</span>
                                </div>
                                @if (($canEditAssessment ?? false) && $poldaResult->rekomendasi === \App\Enums\AssessmentResult::Revision)
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('grant-planning.assessment', $grant)" wire:navigate>
                                        {{ __('page.grant-detail.edit-assessment') }}
                                    </flux:button>
                                @endif
                            </div>
                            @if ($poldaResult->keterangan)
                                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 prose prose-sm dark:prose-invert max-w-none">{!! $poldaResult->keterangan !!}</div>
                            @endif
                        </div>
                    @endif

                    {{-- Mabes Result --}}
                    @if ($mabesResult)
                        <div class="mt-4">
                            <p class="font-semibold text-base text-zinc-700 dark:text-zinc-300 mb-2">{{ __('page.grant-detail.mabes-result') }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @php
                                        $mabesBadgeColor = match ($mabesResult->rekomendasi) {
                                            \App\Enums\AssessmentResult::Fulfilled => 'green',
                                            \App\Enums\AssessmentResult::Revision => 'yellow',
                                            \App\Enums\AssessmentResult::Rejected => 'red',
                                        };
                                    @endphp
                                    <flux:badge size="sm" :color="$mabesBadgeColor">{{ $mabesResult->rekomendasi->label() }}</flux:badge>
                                    @if ($mabesResult->orgUnit)
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $mabesResult->orgUnit->nama_unit }}</span>
                                    @endif
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $mabesResult->created_at->format('d M Y H:i') }}</span>
                                </div>
                                @if (($canEditAssessment ?? false) && $mabesResult->rekomendasi === \App\Enums\AssessmentResult::Revision)
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('grant-planning.assessment', $grant)" wire:navigate>
                                        {{ __('page.grant-detail.edit-assessment') }}
                                    </flux:button>
                                @endif
                            </div>
                            @if ($mabesResult->keterangan)
                                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 prose prose-sm dark:prose-invert max-w-none">{!! $mabesResult->keterangan !!}</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    @else
        <flux:text>{{ __('page.grant-detail.no-assessment-data') }}</flux:text>
    @endif
</div>
