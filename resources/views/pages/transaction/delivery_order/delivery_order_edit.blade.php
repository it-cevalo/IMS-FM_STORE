@extends('layouts.admin')

@section('content')
@php
    $isApproved = $delivery_order->flag_approve === 'Y';
@endphp

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Delivery Order</h6>
    </div>

    <div class="card-body">
        {{-- ALERT --}}
        @if(\Session::has('error'))
            <div class="alert alert-danger">{{ \Session::get('error') }}</div>
        @elseif(\Session::has('success'))
            <div class="alert alert-success">{{ \Session::get('success') }}</div>
        @endif

        <form action="{{ route('delivery_order.update',$delivery_order->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- DATE --}}
            <div class="mb-3">
                <label>Date</label>
                <input class="form-control"
                       type="date"
                       value="{{ \Carbon\Carbon::parse($delivery_order->tgl_do)->format('Y-m-d') }}"
                       readonly>
            </div>

            {{-- NUMBER --}}
            <div class="mb-3">
                <label>DO Number</label>
                <input class="form-control"
                       type="text"
                       value="{{ $delivery_order->no_do }}"
                       readonly>
            </div>

            {{-- NO RESI (SATU-SATUNYA YANG BISA EDIT SETELAH APPROVE) --}}
            <div class="mb-3">
                <label>No Resi</label>
                <input class="form-control"
                       name="no_resi"
                       value="{{ $delivery_order->no_resi }}"
                       {{ !$isApproved ? 'readonly' : '' }}>
            </div>

            {{-- SHIPPING VIA --}}
            <div class="mb-3">
                <label>Shipping Via</label>
                <select class="form-control"
                        name="shipping_via"
                        {{ $isApproved ? 'disabled' : '' }}>
                    @foreach($shipping_via as $k => $v)
                        <option value="{{ $k }}" {{ $delivery_order->shipping_via == $k ? 'selected' : '' }}>
                            {{ $v }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- supaya value tetap terkirim kalau disabled --}}
            @if($isApproved)
                <input type="hidden" name="shipping_via" value="{{ $delivery_order->shipping_via }}">
            @endif

            {{-- NOTE --}}
            <div class="mb-3">
                <label>Note</label>
                <textarea class="form-control"
                          name="reason_do"
                          {{ $isApproved ? 'readonly' : '' }}>{{ $delivery_order->reason_do }}</textarea>
            </div>

            {{-- ===================== --}}
            {{-- DETAIL PRODUK --}}
            {{-- ===================== --}}
            <hr>
            <h6 class="font-weight-bold">Product Detail</h6>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th width="120">Qty</th>
                            @if(!$isApproved)
                                <th width="80">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="productBody">
                        @foreach($tdo_detail as $d)
                        <tr>
                            <td>{{ $d->SKU }}</td>
                            <td>{{ $d->nama_barang }}</td>
                            <td>
                                <input type="number"
                                       name="qty[]"
                                       class="form-control"
                                       value="{{ $d->qty }}"
                                       min="1"
                                       {{ $isApproved ? 'readonly' : '' }}>
                                <input type="hidden"
                                       name="kode_barang[]"
                                       value="{{ $d->kode_barang }}">
                            </td>
                            @if(!$isApproved)
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm btn-remove">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ADD PRODUCT (HANYA JIKA BELUM APPROVE) --}}
            @if(!$isApproved)
                <button type="button" class="btn btn-primary btn-sm" id="addRow">
                    <i class="fa fa-plus"></i> Add Product
                </button>
            @endif

            <hr>

            <button type="submit" class="btn btn-primary">
                Submit
            </button>
            <a href="{{ route('delivery_order.index') }}" class="btn btn-dark">
                Back
            </a>
        </form>
    </div>
</div>

{{-- JS HANYA AKTIF JIKA BELUM APPROVE --}}
@if(!$isApproved)
<script>
document.addEventListener('click', function(e){
    if(e.target.closest('.btn-remove')){
        e.target.closest('tr').remove();
    }
});
</script>
@endif
@endsection
