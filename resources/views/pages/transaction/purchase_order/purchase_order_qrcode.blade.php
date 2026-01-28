<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page {
            size: 33mm 15mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;

            width: 33mm;
            height: 15mm;

            font-family: Arial, sans-serif;
        }

        /* =============================
           SAFE AREA (1mm)
        ============================= */
        .page {
            width: 33mm;
            height: 15mm;
            box-sizing: border-box;

            padding: 1mm; /* ✅ SAFE AREA */

            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* =============================
           CONTENT (CENTERED)
        ============================= */
        .content {
            display: flex;
            align-items: center;
            gap: 2mm;
        }

        /* =============================
           QR
        ============================= */
        .qr {
            width: 10.5mm;
            height: 10.5mm;
            flex-shrink: 0;
        }

        svg {
            width: 100%;
            height: 100%;
        }

        .info {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-weight: 700;
            line-height: 1.15;
            white-space: nowrap;
        }

        /* SKU / NAMA BARANG */
        .info .sku {
            font-size: 10.5pt;
            font-weight: 800;

            /* 🔑 bikin lebih “hitam” di printer */
            -webkit-text-stroke: 0.2px #000;
        }

        /* NOMOR URUT */
        .info .seq {
            font-size: 9.5pt;
            font-weight: 700;

            -webkit-text-stroke: 0.15px #000;
        }
    </style>
</head>

<body>
@foreach ($qrList as $qr)
    <div class="page">
        <div class="content">
            <div class="qr">
                {!! QrCode::format('svg')
                    ->size(300)
                    ->margin(0)
                    ->generate($qr['qr_payload']) !!}
            </div>

            <div class="info">
                <div class="sku">{{ $qr['sku'] }}</div>
                <div class="seq">{{ $qr['nomor_urut'] }}</div>
            </div>
        </div>
    </div>

    @if (! $loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
</body>
</html>
