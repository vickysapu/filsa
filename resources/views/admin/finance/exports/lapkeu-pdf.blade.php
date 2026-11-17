<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan MDF IV Jabar {{ $year ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            font-size: 8pt;
            color: #1a1a1a;
        }

        .page {
            padding: 12mm 10mm 12mm 10mm;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .report-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2mm;
            letter-spacing: 0.5px;
        }

        .report-subtitle {
            text-align: center;
            font-size: 8.5pt;
            color: #444;
            margin-bottom: 4mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        thead tr th {
            background-color: #d9e1f2;
            border: 1px solid #555;
            padding: 3px 4px;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        tbody tr td {
            border: 1px solid #888;
            padding: 2px 4px;
            vertical-align: middle;
        }

        .col-date {
            width: 5%;
            text-align: center;
        }

        .col-desc {
            width: 18%;
        }

        .col-cat {
            width: 6%;
            text-align: right;
        }

        .col-saldo {
            width: 9%;
            text-align: right;
        }

        .group-debit {
            background-color: #e8f0ff;
        }

        .group-kredit {
            background-color: #fdecea;
        }

        tr.opening-row td {
            background: #fff9c4;
            font-style: italic;
            font-weight: bold;
            color: #5d4037;
        }

        tr.total-row td {
            background: #e8f0ff;
            font-weight: bold;
            border-top: 2px solid #444;
        }

        tr.closing-row td {
            background: #d4edda;
            font-weight: bold;
            border-top: 2px solid #28a745;
            color: #155724;
        }

        tbody tr.data-row:nth-child(even) td {
            background-color: #fafbff;
        }

        .footer {
            margin-top: 4mm;
            font-size: 7pt;
            color: #777;
            text-align: right;
        }

        .page-divider {
            border: none;
            border-top: 1.5px solid #c0c0c0;
            margin: 3mm 0 2mm 0;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        @page {
            margin: 0;
        }
    </style>
</head>

<body>
    @foreach ($sections as $section)
        <div class="page">
            <div class="report-title">
                Laporan Keuangan MDF IV Jabar — {{ strtoupper($section['branch_name']) }}
            </div>
            <div class="report-subtitle">
                Periode: {{ $section['bulan_tahun'] }}
            </div>

            <hr class="page-divider">

            <table>
                <thead>
                    <tr>
                        <th rowspan="2" class="col-date">Tgl</th>
                        <th rowspan="2" class="col-desc">Uraian</th>
                        <th colspan="5" class="group-debit">DEBIT (PENERIMAAN)</th>
                        <th colspan="6" class="group-kredit">KREDIT (PENGELUARAN)</th>
                        <th rowspan="2" class="col-saldo">SALDO</th>
                    </tr>
                    <tr>
                        <th class="col-cat">PP</th>
                        <th class="col-cat">Iuran</th>
                        <th class="col-cat">Dana Misi</th>
                        <th class="col-cat">Bunga</th>
                        <th class="col-cat">Lain-Lain</th>

                        <th class="col-cat">BPJS</th>
                        <th class="col-cat">Iuran MDF</th>
                        <th class="col-cat">TAHAKA</th>
                        <th class="col-cat">Pajak Bunga</th>
                        <th class="col-cat">Admin</th>
                        <th class="col-cat">Lain-Lain</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="opening-row">
                        <td class="center"></td>
                        <td>SALDO AWAL</td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right"></td>
                        <td class="right">{{ number_format($section['opening_balance'], 0, ',', '.') }}</td>
                    </tr>

                    @foreach ($section['rows'] as $txRow)
                        <tr class="data-row">
                            <td class="col-date center">{{ $txRow['date'] }}</td>
                            <td>{{ $txRow['desc'] }}</td>
                            <td class="col-cat right">
                                {{ $txRow['pp'] > 0 ? number_format($txRow['pp'], 0, ',', '.') : '' }}</td>
                            <td class="col-cat right">
                                {{ $txRow['iuran'] > 0 ? number_format($txRow['iuran'], 0, ',', '.') : '' }}</td>
                            <td class="col-cat right">
                                {{ $txRow['dana_misi'] > 0 ? number_format($txRow['dana_misi'], 0, ',', '.') : '' }}
                            </td>
                            <td class="col-cat right">
                                {{ $txRow['bunga'] > 0 ? number_format($txRow['bunga'], 0, ',', '.') : '' }}</td>
                            <td class="col-cat right">
                                {{ $txRow['lain_debit'] > 0 ? number_format($txRow['lain_debit'], 0, ',', '.') : '' }}
                            </td>

                            <td class="col-cat right">
                                {{ $txRow['bpjs'] > 0 ? number_format($txRow['bpjs'], 0, ',', '.') : '' }}</td>
                            <td class="col-cat right">
                                {{ $txRow['iuran_mdf'] > 0 ? number_format($txRow['iuran_mdf'], 0, ',', '.') : '' }}
                            </td>
                            <td class="col-cat right">
                                {{ $txRow['tahaka'] > 0 ? number_format($txRow['tahaka'], 0, ',', '.') : '' }}</td>
                            <td class="col-cat right">
                                {{ $txRow['pajak_bunga'] > 0 ? number_format($txRow['pajak_bunga'], 0, ',', '.') : '' }}
                            </td>
                            <td class="col-cat right">
                                {{ $txRow['admin'] > 0 ? number_format($txRow['admin'], 0, ',', '.') : '' }}</td>
                            <td class="col-cat right">
                                {{ $txRow['lain_kredit'] > 0 ? number_format($txRow['lain_kredit'], 0, ',', '.') : '' }}
                            </td>

                            <td class="col-saldo right">{{ number_format($txRow['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <tr class="total-row">
                        <td></td>
                        <td>TOTAL</td>
                        <td class="right">{{ number_format($section['total_pp'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_iuran'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_dana_misi'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_bunga'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_lain_debit'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_bpjs'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_iuran_mdf'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_tahaka'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_pajak_bunga'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_admin'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($section['total_lain_kredit'], 0, ',', '.') }}</td>
                        <td></td>
                    </tr>

                    <tr class="closing-row">
                        <td></td>
                        <td>SALDO AKHIR</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="right">{{ number_format($section['closing_balance'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="footer">
                Dicetak: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; MDF IV Jawa Barat
            </div>
        </div>
    @endforeach
</body>

</html>
