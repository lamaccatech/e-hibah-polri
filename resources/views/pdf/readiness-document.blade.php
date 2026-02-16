@extends('pdf._layout')

@section('content')
    <div class="document-title">Laporan Kesiapan Penerimaan Hibah Langsung</div>

    {{-- Donor Info --}}
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

    {{-- Assessments per Aspect --}}
    <div class="section-title">II. Hasil Pengkajian</div>
    @foreach (\App\Enums\AssessmentAspect::cases() as $aspect)
        @php
            $satkerContents = $satkerAssessments[$aspect->value] ?? null;
            $poldaResult = $poldaResults[$aspect->value] ?? null;
            $mabesResult = $mabesResults[$aspect->value] ?? null;
        @endphp

        <div class="mt-2">
            <p class="font-bold">{{ $loop->iteration }}. Aspek {{ $aspect->label() }}</p>

            @if ($satkerContents && $satkerContents->isNotEmpty())
                <p class="mt-2"><em>Kajian Satuan Kerja:</em></p>
                <div class="content">
                    @foreach ($satkerContents as $content)
                        <p>{!! $content->isi !!}</p>
                    @endforeach
                </div>
            @endif

            @if ($poldaResult)
                <p class="mt-2"><em>Hasil Kajian Satuan Induk:</em> {{ $poldaResult->rekomendasi->label() }}</p>
                @if ($poldaResult->keterangan)
                    <p>{{ $poldaResult->keterangan }}</p>
                @endif
            @endif

            @if ($mabesResult)
                <p class="mt-2"><em>Hasil Kajian Mabes:</em> {{ $mabesResult->rekomendasi->label() }}</p>
                @if ($mabesResult->keterangan)
                    <p>{{ $mabesResult->keterangan }}</p>
                @endif
            @endif
        </div>
    @endforeach

    {{-- Finance Ministry Submission / Harmonization --}}
    @if ($financeMinistrySubmission)
        <div class="section-title">III. Informasi Harmonisasi</div>
        <table class="info-table">
            <tr>
                <td class="label">Penerima Hibah</td>
                <td class="separator">:</td>
                <td>{{ $financeMinistrySubmission->penerima_hibah }}</td>
            </tr>
            <tr>
                <td class="label">Sumber Pembiayaan</td>
                <td class="separator">:</td>
                <td>{{ $financeMinistrySubmission->sumber_pembiayaan }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Pembiayaan</td>
                <td class="separator">:</td>
                <td>{{ $financeMinistrySubmission->jenis_pembiayaan }}</td>
            </tr>
            <tr>
                <td class="label">Cara Penarikan</td>
                <td class="separator">:</td>
                <td>{{ $financeMinistrySubmission->cara_penarikan }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Efektif</td>
                <td class="separator">:</td>
                <td>{{ $financeMinistrySubmission->tanggal_efektif?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Batas Penarikan</td>
                <td class="separator">:</td>
                <td>{{ $financeMinistrySubmission->tanggal_batas_penarikan?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Penutupan Rekening</td>
                <td class="separator">:</td>
                <td>{{ $financeMinistrySubmission->tanggal_penutupan_rekening?->format('d/m/Y') ?? '-' }}</td>
            </tr>
        </table>
    @endif

    {{-- Withdrawal Plans --}}
    @php $sectionNumber = $financeMinistrySubmission ? 'IV' : 'III'; @endphp
    <div class="section-title">{{ $sectionNumber }}. Rencana Penarikan</div>
    @if ($withdrawalPlans->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40pt;">No</th>
                    <th>Uraian</th>
                    <th style="width: 100pt;">Tanggal</th>
                    <th style="width: 120pt; text-align: right;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($withdrawalPlans as $plan)
                    <tr>
                        <td class="text-center">{{ $plan->nomor_urut }}</td>
                        <td>{{ $plan->uraian }}</td>
                        <td class="text-center">{{ $plan->tanggal?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-right">{{ number_format($plan->nilai, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="font-bold">Total</td>
                    <td class="text-right font-bold">{{ number_format($withdrawalPlans->sum('nilai'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p>-</p>
    @endif

    {{-- Location Allocations --}}
    @php $locationSection = $financeMinistrySubmission ? 'V' : 'IV'; @endphp
    <div class="section-title">{{ $locationSection }}. Lokasi dan Alokasi</div>
    @if ($locationAllocations->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40pt;">No</th>
                    <th>Lokasi</th>
                    <th style="width: 120pt; text-align: right;">Alokasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($locationAllocations as $allocation)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $allocation->lokasi }}</td>
                        <td class="text-right">{{ number_format($allocation->alokasi, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="font-bold">Total</td>
                    <td class="text-right font-bold">{{ number_format($locationAllocations->sum('alokasi'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p>-</p>
    @endif

    <p class="mt-4">{{ $orgUnit->nama_unit }}, {{ \Carbon\Carbon::parse($documentDate)->translatedFormat('d F Y') }}</p>
@endsection
