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
    font-weight: normal; /* 🔑 PAKSA SATU VARIAN FONT */
}

/* =============================
   NAMA BARANG (PALING BESAR)
============================= */
.name {
    font-size: 5pt;
    line-height: 1.1;

    white-space: normal;
    word-wrap: break-word;

    /* 🔑 bikin tebal TANPA ganti font */
    -webkit-text-stroke: 0.35px #000;
}

/* SKU */
.sku {
    font-size: 5pt;
    white-space: nowrap;

    -webkit-text-stroke: 0.3px #000;
}

/* NO URUT */
.seq {
    font-size: 5pt;
    white-space: nowrap;

    -webkit-text-stroke: 0.25px #000;
}
</style>
</head>

<body>
@foreach ($qrList as $qr)
@php
    $svg = QrCode::format('svg')
        ->size(300)
        ->margin(0)
        ->generate($qr['qr_payload']);

    $base64 = base64_encode($svg);
@endphp

<div class="page">
    <table>
        <tr>
            <!-- QR -->
            <td class="qr" style="width:11mm">
                <img src="data:image/svg+xml;base64,{{ $base64 }}">
            </td>

            <!-- TEXT -->
            <td>
                <div class="info">
                    <div class="name">
                        <strong>{{ \Illuminate\Support\Str::limit($qr['nama_barang'], 55) }}</strong>
                    </div>
                    <div class="sku">
                        <strong>{{ $qr['sku'] }}</strong>
                    </div>
                    <div class="seq">
                        <strong>{{ $qr['nomor_urut'] }}</strong>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
@endforeach
</body>
</html>
