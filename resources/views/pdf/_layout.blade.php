<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4;
            margin: 2.5cm 2cm 2cm 2.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .uppercase { text-transform: uppercase; }
        .mb-1 { margin-bottom: 4pt; }
        .mb-2 { margin-bottom: 8pt; }
        .mb-4 { margin-bottom: 16pt; }
        .mb-6 { margin-bottom: 24pt; }
        .mt-2 { margin-top: 8pt; }
        .mt-4 { margin-top: 16pt; }
        .mt-6 { margin-top: 24pt; }

        .header {
            text-align: center;
            margin-bottom: 24pt;
        }

        .header-title {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header-unit {
            font-size: 12pt;
            font-weight: bold;
        }

        .document-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            text-transform: uppercase;
            margin-bottom: 24pt;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 16pt;
            margin-bottom: 8pt;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8pt;
            margin-bottom: 8pt;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 4pt 6pt;
            font-size: 11pt;
            vertical-align: top;
        }

        table.data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        table.info-table {
            margin-bottom: 8pt;
        }

        table.info-table td {
            padding: 2pt 4pt;
            vertical-align: top;
            font-size: 12pt;
        }

        table.info-table td.label {
            width: 180pt;
        }

        table.info-table td.separator {
            width: 12pt;
        }

        .signature-block {
            margin-top: 40pt;
            width: 100%;
        }

        .signature-block td {
            vertical-align: top;
        }

        .signature-right {
            text-align: center;
            width: 250pt;
        }

        .signature-space {
            height: 60pt;
        }

        .page-break {
            page-break-before: always;
        }

        .content p {
            margin-bottom: 6pt;
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="header-title">KEPOLISIAN NEGARA REPUBLIK INDONESIA</p>
        <p class="header-unit">{{ $orgUnit->parent?->nama_unit ?? $orgUnit->nama_unit }}</p>
        <p class="header-unit">{{ $orgUnit->parent ? $orgUnit->nama_unit : '' }}</p>
    </div>

    @yield('content')

    <table class="signature-block">
        <tr>
            <td>&nbsp;</td>
            <td class="signature-right">
                <p>{{ $chief->jabatan }}</p>
                @if ($chief->tanda_tangan && Storage::exists($chief->tanda_tangan))
                    <div class="signature-space" style="display: flex; align-items: center; justify-content: center;">
                        <img src="data:image/png;base64,{{ base64_encode(Storage::get($chief->tanda_tangan)) }}" style="max-height: 60pt; max-width: 200pt;">
                    </div>
                @else
                    <div class="signature-space"></div>
                @endif
                <p class="font-bold underline">{{ $chief->nama_lengkap }}</p>
                <p>{{ $chief->pangkat }}</p>
                <p>NRP {{ $chief->nrp }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
