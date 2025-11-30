@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Cetak QR Code Barang</h6>
        <button class="btn btn-dark" onclick="window.print()">Print</button>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($details as $item)
            <div class="col-md-3 text-center border p-2 m-2">
                <strong>{{ $item->product_name }}</strong><br>
                {!! QrCode::size(120)->generate($item->part_number . '|' . $item->product_name) !!}
                <div class="small text-muted mt-2">{{ $item->part_number }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
