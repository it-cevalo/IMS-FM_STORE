@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pengiriman Barang</h6>
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
            <div class="alert alert-danger">
                <span>{{ \Session::get('error') }}</span>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @elseif(\Session::has('success'))
            <div class="alert alert-success">
                <span>{{ \Session::get('success') }}</span>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Header Info --}}
        <div class="mb-3">
            <label>Tanggal</label>
            <input class="form-control" value="{{ \Carbon\Carbon::parse($delivery_order->tgl_do)->format('Y-m-d')}}" type="date" disabled>
        </div>

        <div class="mb-3">
            <label>Nomor</label>
            <input class="form-control" value="{{ $delivery_order->no_do }}" type="text" disabled>
        </div>

        <div class="mb-3">
            <label>No Resi</label>
            <input class="form-control" value="{{ $delivery_order->no_resi }}" type="text" disabled>
        </div>

        <div class="mb-3">
            <label>Metode Pengiriman</label>
            <select class="form-control" disabled>
                @foreach($shipping_via as $k => $v)
                    <option value="{{ $k }}" {{ $delivery_order->shipping_via == $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Catatan</label>
            <textarea class="form-control" disabled>{{ $delivery_order->reason_do }}</textarea>
        </div>

        {{-- Detail Table --}}
        <div class="table-responsive">
            <label>Barang</label>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">SKU</th>
                        <th class="text-center">Nama Barang</th>
                        <th class="text-center">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tdo_detail as $detail)
                        <tr>
                            <td>
                                <input type="text" class="form-control" value="{{ $detail->SKU }}" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $detail->nama_barang }}" disabled>
                            </td>
                            <td>
                                <input type="number" class="form-control text-right" value="{{ $detail->qty }}" disabled>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <a href="{{ route('delivery_order.index') }}" class="btn btn-dark">Kembali</a>
    </div>
</div>
@endsection
