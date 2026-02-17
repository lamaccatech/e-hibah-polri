<div>
    <div class="mb-6 space-y-2">
        <flux:heading size="xl">{{ __('page.mabes-agreement-review.review-title') }}</flux:heading>
        <flux:text>
            <strong>{{ $grant->nama_hibah }}</strong>
            &mdash; {{ $grant->orgUnit?->nama_unit ?? '-' }}
            @if ($grant->orgUnit?->parent)
                &mdash; {{ $grant->orgUnit->parent->nama_unit }}
            @endif
            @if ($grant->donor)
                &mdash; {{ $grant->donor->nama }}
            @endif
            @if ($grant->nilai_hibah)
                &mdash; {{ $grant->mata_uang }} {{ number_format($grant->nilai_hibah, 0, ',', '.') }}
            @endif
        </flux:text>
    </div>

    <div class="space-y-8 max-w-3xl">
        @foreach ($assessments as $assessment)
            <div wire:key="assessment-{{ $assessment->id }}" class="p-5 border rounded-lg border-zinc-200 dark:border-zinc-700 space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ $assessment->aspek->label() }}</flux:heading>

                    @if ($assessment->result)
                        <flux:badge size="sm" :color="match($assessment->result->rekomendasi) {
                            \App\Enums\AssessmentResult::Fulfilled => 'green',
                            \App\Enums\AssessmentResult::Revision => 'yellow',
                            \App\Enums\AssessmentResult::Rejected => 'red',
                        }">
                            {{ $assessment->result->rekomendasi->label() }}
                        </flux:badge>
                    @endif
                </div>

                {{-- Satker's original assessment content --}}
                @if (isset($satkerAssessments[$assessment->aspek->value]))
                    <div class="space-y-3">
                        <flux:text class="font-medium text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wide">
                            {{ __('page.mabes-agreement-review.satker-assessment-heading') }}
                        </flux:text>

                        @foreach ($satkerAssessments[$assessment->aspek->value] as $content)
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg space-y-1">
                                @if ($content->subjudul)
                                    <p class="font-medium text-sm text-zinc-600 dark:text-zinc-400">{{ $content->subjudul }}</p>
                                @endif
                                <div class="prose prose-sm dark:prose-invert max-w-none">
                                    {!! $content->isi !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Polda's assessment result --}}
                @if (isset($poldaAssessments[$assessment->aspek->value]))
                    @php $poldaResult = $poldaAssessments[$assessment->aspek->value]; @endphp
                    <div class="space-y-3">
                        <flux:text class="font-medium text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wide">
                            {{ __('page.mabes-agreement-review.polda-assessment-heading') }}
                        </flux:text>

                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg space-y-2">
                            <flux:badge size="sm" :color="match($poldaResult->rekomendasi) {
                                \App\Enums\AssessmentResult::Fulfilled => 'green',
                                \App\Enums\AssessmentResult::Revision => 'yellow',
                                \App\Enums\AssessmentResult::Rejected => 'red',
                            }">
                                {{ $poldaResult->rekomendasi->label() }}
                            </flux:badge>

                            @if ($poldaResult->keterangan)
                                <div class="prose prose-sm dark:prose-invert max-w-none">
                                    {!! $poldaResult->keterangan !!}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Mabes review result --}}
                @if ($assessment->result?->keterangan)
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg space-y-1">
                        <p class="font-medium text-sm text-amber-700 dark:text-amber-400">{{ __('page.mabes-agreement-review.result-remarks-label') }}</p>
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! $assessment->result->keterangan !!}
                        </div>
                    </div>
                @endif

                @if (! $assessment->result)
                    <div class="pt-1">
                        <flux:button variant="primary" size="sm" wire:click="openResultModal({{ $assessment->id }}, '{{ $assessment->aspek->label() }}')">
                            {{ __('page.mabes-agreement-review.submit-result-button') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <flux:modal wire:model.self="showResultModal" class="min-w-[36rem]">
        <form wire:submit="submitResult" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('page.mabes-agreement-review.submit-result-button') }} Aspek {{ $selectedAspectLabel }}</flux:heading>
            </div>

            <flux:radio.group wire:model.live="result" :label="__('page.mabes-agreement-review.result-label')" variant="cards" class="flex-col">
                <flux:radio value="{{ \App\Enums\AssessmentResult::Fulfilled->value }}" :label="__('page.mabes-agreement-review.result-fulfilled')" />
                <flux:radio value="{{ \App\Enums\AssessmentResult::Revision->value }}" :label="__('page.mabes-agreement-review.result-revision')" />
                <flux:radio value="{{ \App\Enums\AssessmentResult::Rejected->value }}" :label="__('page.mabes-agreement-review.result-rejected')" />
            </flux:radio.group>
            <flux:error name="result" />

            @if ($result && $result !== \App\Enums\AssessmentResult::Fulfilled->value)
                <flux:editor
                    wire:model="remarks"
                    :label="__('page.mabes-agreement-review.result-remarks-label')"
                    toolbar="heading | bold italic underline strike | bullet ordered blockquote | link"
                />
                <flux:error name="remarks" />
            @endif

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">
                    {{ __('page.mabes-agreement-review.submit-result-button') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
