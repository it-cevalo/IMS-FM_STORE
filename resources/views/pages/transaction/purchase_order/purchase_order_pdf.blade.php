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
            font-family: Arial, Helvetica, sans-serif;
        }

        /* ================= LABEL ================= */
        .label {
            position: relative;
            width: 33mm;
            height: 15mm;
            overflow: hidden;
        }

        /* ================= QR ================= */
        .qr {
            position: absolute;
            top: 1mm;
            left: 1mm;
            width: 9mm;
            height: 9mm;
        }

        .qr img {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* ================= TEXT ================= */
        .name {
            position: absolute;
            top: 1.2mm;          /* jangan diubah (biar ga nyentuh QR) */
            left: 11.5mm;
            right: 1mm;

            font-size: 6px;
            font-weight: bold;
            line-height: 1.05;

            white-space: normal;
            word-break: break-word;
            max-height: 4mm;
            overflow: hidden;
        }
        .sku {
            position: absolute;
            top: 5.0mm;          /* ⬆️ NAIK */
            left: 11.5mm;
            right: 1mm;

            font-size: 5px;
            line-height: 1;
            white-space: nowrap;
        }

        /* 🔽 AUTO SHRINK 10% */
        .name.small {
            font-size: 5.4px; /* 6px - 10% */
        }

        /* .seq {
            position: absolute;
            top: 5.4mm;
            left: 11.5mm;

            font-size: 5.5px;
            line-height: 1;
            white-space: nowrap;
        } */
        .seq {
            position: absolute;
            top: 6.8mm;          /* 🔥 INI KUNCINYA */
            left: 11.5mm;

            font-size: 5.5px;
            line-height: 1;
            white-space: nowrap;
        }
    </style>
</head>
<body>

@foreach ($qrList as $q)

    @php
        // threshold aman utk 33mm label
        $isLongName = mb_strlen($q['nama_barang']) > 22;
    @endphp

    <div class="label">
        <div class="qr">
            <img src="data:image/png;base64, {!! base64_encode(
                QrCode::size(200)->margin(0)->generate($q['qr_payload'])
            ) !!}">
        </div>

        <div class="name">
            {{ $q['nama_barang'] }}
        </div>
        
        <div class="sku">
            {{ $q['sku'] }}
        </div>
        
        <div class="seq">
            {{ $q['nomor_urut'] }}
        </div>
    </div>

@endforeach

</body>
</html>
