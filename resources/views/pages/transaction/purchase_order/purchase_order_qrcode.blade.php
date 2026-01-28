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

        /* =============================
           INFO
        ============================= */
        .info {
            font-size: 8pt;
            font-weight: bold;
            line-height: 1.25;
            white-space: nowrap;
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
                {{ $qr['sku'] }}<br>
                {{ $qr['nomor_urut'] }}
            </div>
        </div>
    </div>

    @if (! $loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
</body>
</html>
