<html>
<head>
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        .table, th, td {
            border: none;
            padding: 6px;
        }
        .table {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .no-border {
            border: none;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signature-block { margin-top: 60px; }
        .info-table td { padding: 4px; vertical-align: top; }
    </style>
</head>
<body>
@php
    $tanggal = \Carbon\Carbon::parse($header->tgl_inv)->translatedFormat('d F Y');
@endphp

<!-- Header -->
<div class="header">
    <img src="{{ public_path('assets/img/logo_customer.png') }}" style="max-width: 25%; height: auto;"><br>
    <h2 style="margin: 0;">Strukt Pembayaran</h2>
    <small>No. Invoice: <strong>{{ $header->no_inv }}</strong></small><br>
    <small>{{ $tanggal }}</small>
</div>

<!-- Informasi Pembayaran -->
<table class="no-border info-table" width="100%" style="margin-top: 10px;">
    <tr>
        <td style="width: 20%"><strong>Customer</strong></td>
        <td style="width: 2%">:</td>
        <td>{{ $header->nama_cust }}</td>
    </tr>
    <tr>
        <td><strong>Alamat</strong></td>
        <td>:</td>
        <td>{{ $header->address_cust }}</td>
    </tr>
    <tr>
        <td><strong>Nilai</strong></td>
        <td>:</td>
        <td>
            <strong>Rp {{ number_format($header->grand_total, 2, ',', '.') }}</strong><br>
            <em>{{ $terbilang }} rupiah</em>
        </td>
    </tr>
    <tr>
        <td><strong>Metode Pembayaran</strong></td>
        <td>:</td>
        <td>Transfer ke <strong>{{$header->nama_bank}}</strong> - <strong>{{ $header->norek_bank }}</strong></td>
    </tr>
</table>

<!-- Daftar Produk -->
<table class="table">
    <thead>
        <tr>
            <th style="width: 20%;" class="text-center">Kode Barang</th>
            <th>Nama Barang</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Subtotal</th>
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
        <tr>
            <td colspan="4" class="text-right"><strong>Diskon</strong></td>
            <td class="text-right">Rp {{ number_format($header->diskon, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right"><strong>PPN</strong></td>
            <td class="text-right">Rp {{ number_format($header->ppn, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right"><strong>Grand Total</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($header->grand_total, 2, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

</body>
</html>