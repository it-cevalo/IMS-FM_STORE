<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR Code PO</title>

    <style>
        @page { margin: 20px; }

        body {
            font-family: sans-serif;
            font-size: 14px;
        }

        .page {
            page-break-after: always;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .qr-container {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .qr-box img {
            width: 180px;
            height: 180px;
            border: 1px solid #000;
            padding: 5px;
        }

        .info-box {
            font-size: 16px;
            line-height: 1.5;
        }

        .label {
            font-weight: bold;
            width: 130px;
            display: inline-block;
        }

        .line {
            display: flex;
        }
    </style>
</head>
<body>

@foreach ($qrList as $i => $q)

<div class="page">

    <div class="qr-container">

        <!-- QR CODE -->
        <div class="qr-box">
            <img src="data:image/png;base64, {!! base64_encode(
                QrCode::size(500)->margin(1)->generate($q['qr_payload'])
            ) !!}">
        </div>

        <!-- INFO BARANG -->
        <div class="info-box">
            <div class="line"><span class="label">Nama Barang:</span> {{ $q['nama_barang'] }}</div>
            <div class="line"><span class="label">Kode Barang:</span> {{ $q['kode_barang'] }}</div>
            <div class="line"><span class="label">Nomor Urut:</span> {{ $q['nomor_urut'] }}</div>
        </div>

    </div>

</div>

@endforeach

</body>
</html>
