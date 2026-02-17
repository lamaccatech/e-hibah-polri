@php
    use App\Enums\ProposalChapter;

    // Agreement chapters include ReceptionBasis and SupervisionMechanism (which planning excludes)
    $agreementChapterEnums = [
        ProposalChapter::ReceptionBasis,
        ProposalChapter::SupervisionMechanism,
    ];

    // Standard planning chapters (same as proposal tab, for shared content)
    $sharedChapters = array_filter(
        ProposalChapter::cases(),
        fn (ProposalChapter $c) => !in_array($c, [ProposalChapter::ReceptionBasis, ProposalChapter::SupervisionMechanism]),
    );

    // Key chapters by judul for quick lookup
    $chaptersByKey = $agreementChapters->keyBy('judul');

    // Custom chapters = those not matching any ProposalChapter enum value
    $enumValues = array_map(fn (ProposalChapter $c) => $c->value, ProposalChapter::cases());
    $customChapters = $agreementChapters->filter(fn ($ch) => !in_array($ch->judul, $enumValues));

    $hasAnyData = $agreementChapters->isNotEmpty() || $budgetPlans->isNotEmpty() || $activitySchedules->isNotEmpty();
@endphp

<div class="max-w-3xl space-y-8">
    @if ($hasAnyData)
        {{-- Agreement-specific chapters first --}}
        @foreach ($agreementChapterEnums as $enum)
            @php $chapter = $chaptersByKey->get($enum->value); @endphp
            @if ($chapter)
                <div>
                    <flux:heading size="xl">{{ $enum->label() }}</flux:heading>
                    @if ($chapter->contents->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach ($chapter->contents as $content)
                                @if ($content->subjudul)
                                    <p class="font-semibold text-base text-zinc-700 dark:text-zinc-300">{{ $content->subjudul }}</p>
                                @endif
                                <div class="text-sm text-zinc-600 dark:text-zinc-400 prose prose-sm dark:prose-invert max-w-none">{!! $content->isi !!}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        @endforeach

        {{-- Shared chapters in enum order --}}
        @foreach ($sharedChapters as $enum)
            @if ($enum === ProposalChapter::BudgetPlan)
                {{-- Schedule table before budget --}}
                <div>
                    <flux:heading size="xl" class="mb-4">{{ __('page.grant-detail.section-schedule') }}</flux:heading>

                    @if ($activitySchedules->isNotEmpty())
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('page.grant-detail.column-schedule-activity') }}</flux:table.column>
                                <flux:table.column>{{ __('page.grant-detail.column-schedule-start') }}</flux:table.column>
                                <flux:table.column>{{ __('page.grant-detail.column-schedule-end') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($activitySchedules as $schedule)
                                    <flux:table.row>
                                        <flux:table.cell>{{ $schedule->uraian_kegiatan }}</flux:table.cell>
                                        <flux:table.cell>{{ $schedule->tanggal_mulai?->format('d M Y') ?? '-' }}</flux:table.cell>
                                        <flux:table.cell>{{ $schedule->tanggal_selesai?->format('d M Y') ?? '-' }}</flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <flux:text>{{ __('page.grant-detail.no-schedule-data') }}</flux:text>
                    @endif
                </div>

                {{-- Budget table in place of the BudgetPlan chapter --}}
                <div>
                    <flux:heading size="xl" class="mb-4">{{ $enum->label() }}</flux:heading>

                    @if ($budgetPlans->isNotEmpty())
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>No</flux:table.column>
                                <flux:table.column>{{ __('page.grant-detail.column-budget-description') }}</flux:table.column>
                                <flux:table.column align="end">{{ __('page.grant-detail.column-budget-value') }}</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($budgetPlans as $plan)
                                    <flux:table.row>
                                        <flux:table.cell>{{ $plan->nomor_urut }}</flux:table.cell>
                                        <flux:table.cell>{{ $plan->uraian }}</flux:table.cell>
                                        <flux:table.cell align="end">{{ number_format($plan->nilai, 0, ',', '.') }}</flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                                <flux:table.row>
                                    <flux:table.cell colspan="2" class="font-bold">Total</flux:table.cell>
                                    <flux:table.cell align="end" class="font-bold">{{ number_format($budgetPlans->sum('nilai'), 0, ',', '.') }}</flux:table.cell>
                                </flux:table.row>
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <flux:text>{{ __('page.grant-detail.no-budget-data') }}</flux:text>
                    @endif
                </div>
            @else
                @php $chapter = $chaptersByKey->get($enum->value); @endphp
                @if ($chapter)
                    <div>
                        <flux:heading size="xl">{{ $enum->label() }}</flux:heading>
                        @if ($chapter->contents->isNotEmpty())
                            <div class="mt-3 space-y-2">
                                @foreach ($chapter->contents as $content)
                                    @if ($content->subjudul)
                                        <p class="font-semibold text-base text-zinc-700 dark:text-zinc-300">{{ $content->subjudul }}</p>
                                    @endif
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400 prose prose-sm dark:prose-invert max-w-none">{!! $content->isi !!}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        @endforeach

        {{-- Custom chapters --}}
        @foreach ($customChapters as $chapter)
            <div>
                <flux:heading size="xl">{{ $chapter->judul }}</flux:heading>
                @if ($chapter->contents->isNotEmpty())
                    <div class="mt-3 space-y-2">
                        @foreach ($chapter->contents as $content)
                            @if ($content->subjudul)
                                <p class="font-semibold text-base text-zinc-700 dark:text-zinc-300">{{ $content->subjudul }}</p>
                            @endif
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 prose prose-sm dark:prose-invert max-w-none">{!! $content->isi !!}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

    @else
        <flux:text>{{ __('page.grant-detail.no-agreement-data') }}</flux:text>
    @endif
</div>
