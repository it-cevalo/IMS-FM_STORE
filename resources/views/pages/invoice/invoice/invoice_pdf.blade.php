<html>
<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .table, th, td {
            border: 1px solid #000;
            border-collapse: collapse;
            padding: 6px;
        }
        .no-border {
            border: none !important;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .info-list { list-style: none; padding-left: 0; margin: 0; }
        .info-list li { line-height: 1.4; margin: 0; padding: 0; }
        .section-title {
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 13px;
        }
        .no-margin {
            margin: 0 !important;
        }
        .signature-block {
            margin-top: 70px; /* Jarak jauh antara "Hormat Kami," dan nama */
        }
    </style>
</head>
<body>
@php
    $tgl = \Carbon\Carbon::parse($header->tgl_inv)->translatedFormat('d F Y');
@endphp

<!-- Header -->
<table class="no-border" width="100%">
    <tr class="no-border">
        <td class="no-border" style="width: 60%;">
            <img src="{{ public_path('assets/img/logo_customer.png') }}" style="max-width: 30%; height: auto;" />
        </td>
        <td class="no-border text-right" style="vertical-align: bottom;">
            <h2 style="margin: 0;">INVOICE</h2>
            <div>{{ $tgl }}</div>
        </td>
    </tr>
</table>

<hr>

<!-- Informasi Invoice -->
<table class="no-border" width="100%" style="margin-top: 10px; margin-bottom: 10px;">
    <tr class="no-border">
        <td class="no-border" style="width: 50%;">
            <ul class="info-list">
                <li><strong>No. Invoice:</strong> {{ $header->no_inv }}</li>
                <li><strong>Nama Customer:</strong> {{ $header->nama_cust }}</li>
                <li><strong>NPWP:</strong> {{ $header->npwp_cust }}</li>
            </ul>
        </td>
        <td class="no-border">
            <div class="section-title">Alamat Pengiriman</div>
            <ul class="info-list">
                <li>{{ $header->nama_cust }}</li>
                <li>{{ $header->address_cust }}</li>
            </ul>
        </td>
    </tr>
</table>

<!-- Tabel Produk -->
<table width="100%" class="table">
    <thead>
        <tr>
            <th class="text-center">Kode Barang</th>
            <th>Nama Barang</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Harga Satuan</th>
            <th class="text-right">Total Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice as $item)
        <tr>
            <td class="text-center">{{ $item->SKU }}</td>
            <td>{{ $item->nama_barang }}</td>
            <td class="text-center">{{ $item->qty }}</td>
            <td class="text-right">Rp {{ number_format($item->price, 2, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($item->total_price, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-right font-bold">Diskon:</td>
            <td class="text-right">Rp {{ number_format($header->diskon, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold">PPN 11% :</td>
            <td class="text-right">Rp {{ number_format($header->ppn, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold">Grand Total:</td>
            <td class="text-right font-bold">Rp {{ $grand_total }}</td>
        </tr>
    </tfoot>
</table>

<!-- Informasi Pembayaran & Tanda Tangan -->
<table class="no-border" width="100%" style="margin-top: 30px;">
    <tr class="no-border">
        <!-- Kolom Pembayaran -->
        <td class="no-border" style="width: 50%; vertical-align: top;">
            <p class="no-margin"><strong>Silakan lakukan pembayaran ke rekening berikut:</strong></p>
            <ul class="info-list">
                <li><strong>Bank:</strong> {{ $header->nama_bank }}</li>
                <li><strong>No. Rekening:</strong> {{ $header->norek_bank }}</li>
                <li><strong>Atas Nama:</strong> {{ $header->atasnama_bank }}</li>
            </ul>
        </td>

        <!-- Kolom Tanda Tangan -->
        <td class="no-border text-center" style="width: 50%; vertical-align: top;">
            <div class="section-title">Hormat Kami,</div>
            @if($signed)
                <div class="signature-block">
                    <img src="{{ public_path($signed) }}" width="100px" height="70px" alt="signature"><br>
                </div>
            @else
                <div class="signature-block" style="font-size: 10px;">(Tertanda)</div><br>
            @endif
            <div style="margin-top: 5px;"><strong>nama</strong></div>
        </td>
    </tr>
</table>

</body>
</html>
