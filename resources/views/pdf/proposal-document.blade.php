@extends('pdf._layout')

@section('content')
    @php
        use App\Enums\ProposalChapter;

        $romanNumerals = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV'];

        $planningChapters = array_filter(
            ProposalChapter::cases(),
            fn (ProposalChapter $c) => !in_array($c, [ProposalChapter::ReceptionBasis, ProposalChapter::SupervisionMechanism]),
        );

        $chaptersByKey = $chapters->keyBy('judul');
        $enumValues = array_map(fn (ProposalChapter $c) => $c->value, ProposalChapter::cases());
        $customChapters = $chapters->filter(fn ($ch) => !in_array($ch->judul, $enumValues));

        $chapterNumber = 1;
    @endphp

    <div class="document-title">Naskah Usulan Penerimaan Hibah Langsung</div>

    @if ($planningNumber)
        <table class="info-table mb-4">
            <tr>
                <td class="label">Nomor Perencanaan</td>
                <td class="separator">:</td>
                <td>{{ $planningNumber }}</td>
            </tr>
        </table>
    @endif

    @foreach ($planningChapters as $enum)
        @if ($enum === ProposalChapter::BudgetPlan)
            {{-- Schedule table before budget --}}
            <div class="section-title">{{ $romanNumerals[$chapterNumber] }}. Timeline Kegiatan</div>
            @if ($activitySchedules->isNotEmpty())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40pt;">No</th>
                            <th>Uraian Kegiatan</th>
                            <th style="width: 100pt;">Tanggal Mulai</th>
                            <th style="width: 100pt;">Tanggal Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activitySchedules as $schedule)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $schedule->uraian_kegiatan }}</td>
                                <td class="text-center">{{ $schedule->tanggal_mulai?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-center">{{ $schedule->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>-</p>
            @endif

            {{-- Budget table --}}
            @php $chapterNumber++; @endphp
            <div class="section-title">{{ $romanNumerals[$chapterNumber] }}. {{ $enum->label() }}</div>
            @if ($budgetPlans->isNotEmpty())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40pt;">No</th>
                            <th>Uraian</th>
                            <th style="width: 120pt; text-align: right;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($budgetPlans as $plan)
                            <tr>
                                <td class="text-center">{{ $plan->nomor_urut }}</td>
                                <td>{{ $plan->uraian }}</td>
                                <td class="text-right">{{ number_format($plan->nilai, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="font-bold">Total</td>
                            <td class="text-right font-bold">{{ number_format($budgetPlans->sum('nilai'), 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p>-</p>
            @endif
        @else
            @php $chapter = $chaptersByKey->get($enum->value); @endphp
            @if ($chapter)
                <div class="section-title">{{ $romanNumerals[$chapterNumber] }}. {{ $enum->label() }}</div>
                @if ($chapter->contents->isNotEmpty())
                    <div class="content">
                        @foreach ($chapter->contents as $content)
                            @if ($content->subjudul)
                                <p class="font-bold">{{ $content->subjudul }}</p>
                            @endif
                            <p>{!! $content->isi !!}</p>
                        @endforeach
                    </div>
                @endif
            @endif
        @endif
        @php $chapterNumber++; @endphp
    @endforeach

    {{-- Custom chapters --}}
    @foreach ($customChapters as $chapter)
        <div class="section-title">{{ $romanNumerals[$chapterNumber] ?? $chapterNumber }}. {{ $chapter->judul }}</div>
        @if ($chapter->contents->isNotEmpty())
            <div class="content">
                @foreach ($chapter->contents as $content)
                    @if ($content->subjudul)
                        <p class="font-bold">{{ $content->subjudul }}</p>
                    @endif
                    <p>{!! $content->isi !!}</p>
                @endforeach
            </div>
        @endif
        @php $chapterNumber++; @endphp
    @endforeach

    <p class="mt-4">{{ $orgUnit->nama_unit }}, {{ \Carbon\Carbon::parse($documentDate)->translatedFormat('d F Y') }}</p>
@endsection
