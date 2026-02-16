@extends('pdf._layout')

@section('content')
    <div class="document-title">Kajian Usulan Penerimaan Hibah</div>

    {{-- Donor Identity --}}
    <div class="section-title">I. Identitas Calon Pemberi Hibah</div>
    @if ($donor)
        <table class="info-table">
            <tr>
                <td class="label">Nama</td>
                <td class="separator">:</td>
                <td>{{ $donor->nama }}</td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td class="separator">:</td>
                <td>{{ $donor->alamat }}</td>
            </tr>
            <tr>
                <td class="label">Asal</td>
                <td class="separator">:</td>
                <td>{{ $donor->asal }}</td>
            </tr>
            <tr>
                <td class="label">Kategori</td>
                <td class="separator">:</td>
                <td>{{ $donor->kategori }}</td>
            </tr>
        </table>
    @endif

    {{-- Purpose --}}
    <div class="section-title">II. Maksud</div>
    @if ($purposeChapter && $purposeChapter->contents->isNotEmpty())
        <div class="content">
            @foreach ($purposeChapter->contents as $content)
                <p>{!! $content->isi !!}</p>
            @endforeach
        </div>
    @else
        <p>-</p>
    @endif

    {{-- Objective --}}
    <div class="section-title">III. Tujuan</div>
    @if ($objectiveChapter && $objectiveChapter->contents->isNotEmpty())
        <div class="content">
            @foreach ($objectiveChapter->contents as $content)
                <p>{!! $content->isi !!}</p>
            @endforeach
        </div>
    @else
        <p>-</p>
    @endif

    {{-- Budget --}}
    <div class="section-title">IV. Rencana Anggaran Biaya</div>
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

    {{-- Assessments per Aspect --}}
    <div class="section-title">V. Hasil Kajian</div>
    @foreach (\App\Enums\AssessmentAspect::cases() as $aspect)
        @php $contents = $satkerAssessments[$aspect->value] ?? null; @endphp
        <div class="mt-2">
            <p class="font-bold">{{ $loop->iteration }}. Aspek {{ $aspect->label() }}</p>
            @if ($contents && $contents->isNotEmpty())
                <div class="content">
                    @foreach ($contents as $content)
                        <p>{!! $content->isi !!}</p>
                    @endforeach
                </div>
            @else
                <p>-</p>
            @endif
        </div>
    @endforeach

    <p class="mt-4">{{ $orgUnit->nama_unit }}, {{ \Carbon\Carbon::parse($documentDate)->translatedFormat('d F Y') }}</p>
@endsection
