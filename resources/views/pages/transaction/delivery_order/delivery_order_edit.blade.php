@extends('layouts.admin')

@section('content')
@php
    $isApproved = $delivery_order->flag_approve === 'Y';
@endphp

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pengiriman Barang</h6>
    </div>

    <div class="card-body">

        {{-- ALERT --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @elseif(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('delivery_order.update',$delivery_order->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- DATE --}}
            <div class="mb-3">
                <label>Tanggal</label>
                <input type="date" class="form-control"
                       value="{{ \Carbon\Carbon::parse($delivery_order->tgl_do)->format('Y-m-d') }}" readonly>
            </div>

            {{-- NUMBER --}}
            <div class="mb-3">
                <label>Nomor</label>
                <input type="text" class="form-control"
                       value="{{ $delivery_order->no_do }}" readonly>
            </div>

            {{-- NO RESI --}}
            <div class="mb-3">
                <label>No Resi</label>
                <input type="text" class="form-control"
                       name="no_resi"
                       value="{{ $delivery_order->no_resi }}">
            </div>

            {{-- Metode Pengiriman --}}
            <div class="mb-3">
                <label>Metode Pengiriman</label>
                <select class="form-control" name="shipping_via" {{ $isApproved ? 'disabled' : '' }}>
                    @foreach($shipping_via as $k=>$v)
                        <option value="{{ $k }}" {{ $delivery_order->shipping_via==$k?'selected':'' }}>
                            {{ $v }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($isApproved)
                <input type="hidden" name="shipping_via" value="{{ $delivery_order->shipping_via }}">
            @endif

            {{-- NOTE --}}
            <div class="mb-3">
                <label>Catatan</label>
                <textarea class="form-control" name="reason_do"
                    {{ $isApproved?'readonly':'' }}>{{ $delivery_order->reason_do }}</textarea>
            </div>

            {{-- ================= PRODUCT ================= --}}
            <hr>
            <label class="font-weight-bold">Produk</label>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Qty Tersedia</th>
                            @if(!$isApproved)
                                <th style="width:60px" class="text-center align-middle">
                                    <a class="btn btn-primary btn-sm" id="addrow">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="append_akun">
                        {{-- EXISTING DETAIL --}}
                        @foreach($tdo_detail as $d)
                        <tr>
                            <td>
                                <select class="form-control select2 sku" name="sku[]" {{ $isApproved?'disabled':'' }}>
                                    <option value="{{ $d->sku }}" selected>{{ $d->SKU }}</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control nama_barang"
                                       value="{{ $d->nama_barang }}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control qty"
                                       name="qty[]" min="1"
                                       value="{{ $d->qty }}"
                                       {{ $isApproved?'readonly':'' }}>
                            </td>
                            <td>
                                <input type="number" class="form-control qty_tersedia" readonly>
                            </td>
                            @if(!$isApproved)
                            <td class="text-center align-middle">
                                <a class="btn btn-danger btn-sm btn-remove">
                                    <i class="fa fa-minus"></i>
                                </a>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('delivery_order.index') }}" class="btn btn-dark">Kembali</a>
        </form>
    </div>
</div>
@if(!$isApproved)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){

    $('.select2').select2({ width:'100%' });

    const PRODUCTS = @json($products);

    function getSkuOptions(except = []) {
        let opt = `<option value="">-- Pilih SKU --</option>`;
        PRODUCTS.forEach(p => {
            if (!except.includes(p.sku)) {
                opt += `<option value="${p.sku}"
                               data-kode="${p.sku}"
                               data-nama="${p.nama_barang}">
                               ${p.SKU}
                        </option>`;
            }
        });
        return opt;
    }

    function refreshSku(){
        let selected = [];
        $('.sku').each(function(){ if($(this).val()) selected.push($(this).val()); });
        $('.sku').each(function(){
            let current = $(this).val();
            let except = selected.filter(v => v !== current);
            $(this).html(getSkuOptions(except));
            $(this).val(current).trigger('change.select2');
        });
    }

    function loadStock(row, sku){
        if(!sku) return;
        $.get('/delivery-order/stock',{ sku }, function(res){
            row.find('.qty_tersedia').val(res.qty_tersedia || 0);
        });
    }

    // load stok existing
    $('#append_akun tr').each(function(){
        let sku = $(this).find('.sku').val();
        loadStock($(this), sku);
    });

    // ADD ROW
    $('#addrow').click(function(e){
        e.preventDefault();
        let usedSku = [];
        $('.sku').each(function(){ if($(this).val()) usedSku.push($(this).val()); });

        let row = `
        <tr>
            <td>
                <select class="form-control select2 sku" name="sku[]" required>
                    ${getSkuOptions(usedSku)}
                </select>
            </td>
            <td><input type="text" class="form-control nama_barang" readonly></td>
            <td><input type="number" class="form-control qty" name="qty[]" min="1" required></td>
            <td><input type="number" class="form-control qty_tersedia" readonly></td>
            <td class="text-center align-middle">
                <a class="btn btn-danger btn-sm btn-remove">
                    <i class="fa fa-minus"></i>
                </a>
            </td>
        </tr>`;
        $('#append_akun').append(row);
        $('#append_akun').find('.select2').last().select2({ width:'100%' });
    });

    // CHANGE SKU
    $('#append_akun').on('change','.sku',function(){
        let row = $(this).closest('tr');
        let opt = $(this).find(':selected');
        let sku = opt.data('kode') || '';
        row.find('.nama_barang').val(opt.data('nama') || '');
        loadStock(row, sku);
        refreshSku();
    });

    // VALIDASI QTY
    $('#append_akun').on('input','.qty',function(){
        let row = $(this).closest('tr');
        let max = parseFloat(row.find('.qty_tersedia').val()) || 0;
        let val = parseFloat($(this).val()) || 0;
        if(val > max){
            $(this).val(max);
            Swal.fire('Warning',`Qty tidak boleh lebih dari ${max}`,'warning');
        }
    });

    // REMOVE
    $('#append_akun').on('click','.btn-remove',function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
        refreshSku();
    });

});
</script>
@endif
@endsection
