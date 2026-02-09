<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR Label</title>

    <style>
        @page {
            size: 33mm 15mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans";
        }

        /* =============================
        1 LABEL = 1 PAGE
        ============================= */
        .page {
            width: 33mm;
            padding: 1mm;
            box-sizing: border-box;
        }

        /* page break antar label */
        .page + .page {
            page-break-before: always;
        }

        /* TABLE ENGINE (PALING STABIL) */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 0;
            vertical-align: top;
        }

        /* QR */
        .qr img {
            width: 10.5mm;
            height: 10.5mm;
            display: block;
        }

        /* TEXT WRAPPER */
        .info {
            line-height: 1.05;
            font-family: "DejaVu Sans";
            font-weight: normal;
        }

        /* =============================
        NAMA BARANG (PALING BESAR)
        ============================= */
        .info-box {
            width: 20mm;          /* INI BATAS MERAH */
            height: 10.5mm;

            box-sizing: border-box;
            padding-right: 1mm;   /* jarak aman kanan */
            padding-bottom: 2mm;  /* jarak aman bawah */

            overflow: hidden;
        }

        /*fix bugs*/

        .name {
            font-size: 4pt;
            line-height: 1.2;

            white-space: normal;
            word-break: break-word;

            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;

            overflow: hidden;
            margin-bottom: 0.6mm;

            -webkit-text-stroke: 0.3px #000;
        }
        /* SKU */
        /* .sku {
            font-size: 5pt;
            white-space: nowrap;

            -webkit-text-stroke: 0.3px #000;
        } */
        .sku {
            font-size: 5pt;
            white-space: nowrap;
            -webkit-text-stroke: 0.3px #000;
        }


        /* NO URUT */
        /* .seq {
            font-size: 5pt;
            white-space: nowrap;

            -webkit-text-stroke: 0.25px #000;
        } */

        .seq {
            font-size: 5pt;
            white-space: nowrap;
            -webkit-text-stroke: 0.25px #000;
        }
    </style>
</head>
<body>

@foreach ($qrList as $q)
@php
    $svg = QrCode::format('svg')
        ->size(300)
        ->margin(0)
        ->generate($q['qr_payload']);

    $base64 = base64_encode($svg);
@endphp
    <div class="page">
        <table>
            <tr>
                <td class="qr" style="width:11mm">
                    <img src="data:image/svg+xml;base64,{{ $base64 }}">
                </td>
                <td style="width:20mm; height:10.5mm;">
                    <div class="info-box">
                        <div class="name">
                            <strong>{{ $q['nama_barang'] }}</strong>
                        </div>
                        <div class="sku">
                            <strong>{{ $q['sku'] }}</strong>
                        </div>
                        <div class="seq">
                            <strong>{{ $q['nomor_urut'] }}</strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
