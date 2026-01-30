<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock In</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }
        .header {
            width: 100%;
            margin-bottom: 15px;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .table th {
            background: #f0f0f0;
            text-align: center;
        }
        .center {
            text-align: center;
        }
        .sign {
            width: 100%;
            margin-top: 40px;
        }
        .sign td {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="title">Purchase Order</div>

    <table class="header">
        <tr>
            <td width="60%">
                <strong>Pemasok</strong><br>
                {{ $po->code_spl }} - {{ $po->nama_spl }}<br>
                {{ $po->supplier->address_spl ?? '-' }}<br>
                Telp: {{ $po->supplier->phone ?? '-' }}
            </td>
            <td width="40%">
                <table class="info-table">
                    <tr>
                        <td>No PO</td>
                        <td>: {{ $po->no_po }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ \Carbon\Carbon::parse($po->tgl_po)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>:
                            @switch($po->status_po)
                                @case('0') Dibuat @break
                                @case('1') Proses @break
                                @case('2') Berkala @break
                                @case('3') Lengkap @break
                                @case('4') Terkonfirmasi @break
                                @case('5') Dibatalkan @break
                                @default Unknown
                            @endswitch
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Kode Barang</th>
                <th>Nama Barang</th>
                <th width="10%">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $row)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $row->part_number }}</td>
                <td>{{ $row->product_name }}</td>
                <td class="center">{{ $row->qty }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td width="33%">
                Dibuat Oleh<br><br><br>
                ( __________________ )
            </td>
            <td width="33%">
                Disetujui<br><br><br>
                ( __________________ )
            </td>
            <td width="33%">
                Pemasok<br><br><br>
                ( __________________ )
            </td>
        </tr>
    </table>

</body>
</html>
