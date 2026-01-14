@extends('layouts.admin')

@section('content')
@php
    $status = $purchase_order->status_po;

    $canEditAll      = $status == 0;
    $canEditHeader   = in_array($status, [0,4]);
    $canEditQtyExtra = $status == 2;
    $isLocked        = $status == 3;
@endphp

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Pemesanan Barang</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('purchase_order.update',$purchase_order->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ================= HEADER ================= --}}
            <div class="mb-3">
                <label>Pemasok *</label>
                <select class="form-control select2" name="id_supplier"
                    {{ !$canEditHeader ? 'disabled' : '' }}>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}"
                            {{ $purchase_order->id_supplier == $sup->id ? 'selected' : '' }}>
                            {{ $sup->code_spl }} - {{ $sup->nama_spl }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Tanggal *</label>
                <input type="date" class="form-control" name="tgl_po"
                    value="{{ \Carbon\Carbon::parse($purchase_order->tgl_po)->format('Y-m-d') }}"
                    {{ !$canEditHeader ? 'readonly' : '' }}>
            </div>

            <div class="mb-3">
                <label>PO Number *</label>
                <input type="text" class="form-control" name="no_po"
                    value="{{ $purchase_order->no_po }}" readonly>
            </div>

            <div class="mb-3">
                <label>Catatan *</label>
                <textarea class="form-control" name="reason_po"
                    {{ !$canEditHeader ? 'readonly' : '' }}>{{ $purchase_order->reason_po }}</textarea>
            </div>

            {{-- ================= DETAIL ================= --}}
            <div class="table-responsive">
                <label>Produk</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width:180px">SKU</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th style="width:90px">Qty Order</th>
                            <th style="width:110px">Qty Lebihan</th>
                            <th>
                                @if($canEditAll)
                                <a class="btn btn-primary btn-sm" id="addrow">
                                    <i class="fa fa-plus"></i>
                                </a>
                                @endif
                            </th>
                        </tr>
                    </thead>

                    <tbody id="append_akun">
                        @foreach($purchase_order_dtl as $val)
                        <tr>
                            <td>
                                <select class="form-control select2 sku"
                                    name="kode_barang[]"
                                    {{ !$canEditAll ? 'disabled' : '' }}>
                                    @foreach($products as $p)
                                        <option value="{{ $p->kode_barang }}"
                                            data-kode="{{ $p->kode_barang }}"
                                            data-nama="{{ $p->nama_barang }}"
                                            {{ $val->part_number == $p->kode_barang ? 'selected' : '' }}>
                                            {{ $p->sku }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text" class="form-control kode_barang"
                                    value="{{ $val->part_number }}" readonly>
                            </td>

                            <td>
                                <input type="text" class="form-control nama_barang"
                                    value="{{ $val->product_name }}" readonly>
                            </td>

                            <td>
                                <input type="number" class="form-control text-right"
                                    name="qty[]" value="{{ $val->qty }}"
                                    {{ !$canEditAll ? 'readonly' : '' }}>
                            </td>

                            <td>
                                <input type="number" class="form-control text-right"
                                    name="qty_extra[]" value="{{ $val->qty_extra ?? 0 }}"
                                    {{ !$canEditQtyExtra ? 'readonly' : '' }}>
                            </td>

                            <td class="text-center">
                                @if($canEditAll)
                                <a class="btn btn-danger btn-sm btn-remove">
                                    <i class="fa fa-minus"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ================= ACTION ================= --}}
            @if(!$isLocked)
                <button type="submit" class="btn btn-primary">Simpan</button>
            @endif
            <a href="{{ route('purchase_order.index') }}" class="btn btn-dark">Kembali</a>

            @if($isLocked)
                <div class="alert alert-warning mt-3">
                    PO ini sudah <b>PARTIAL</b>, tidak bisa diedit.
                </div>
            @endif
        </form>
    </div>
</div>

{{-- ================= JS ================= --}}
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){

    $('.select2').select2({ width:'100%' });

    // =========================
    // SKU CHANGE
    // =========================
    $('#append_akun').on('change','.sku',function(){
        let row = $(this).closest('tr');
        let opt = $(this).find(':selected');

        row.find('.kode_barang').val(opt.data('kode') || '');
        row.find('.nama_barang').val(opt.data('nama') || '');

        refreshSku();
    });

    // =========================
    // ADD ROW
    // =========================
    $('#addrow').click(function(e){
        e.preventDefault();

        let row = `
        <tr>
            <td>
                <select class="form-control select2 sku" name="kode_barang[]">
                    @foreach($products as $p)
                        <option value="{{ $p->kode_barang }}"
                            data-kode="{{ $p->kode_barang }}"
                            data-nama="{{ $p->nama_barang }}">
                            {{ $p->sku }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" class="form-control kode_barang" readonly></td>
            <td><input type="text" class="form-control nama_barang" readonly></td>
            <td><input type="number" class="form-control text-right" name="qty[]"></td>
            <td><input type="number" class="form-control text-right" name="qty_extra[]" value="0" readonly></td>
            <td class="text-center">
                <a class="btn btn-danger btn-sm btn-remove">
                    <i class="fa fa-minus"></i>
                </a>
            </td>
        </tr>`;

        $('#append_akun').append(row);
        $('#append_akun').find('.select2').last().select2({ width:'100%' });
        refreshSku();
    });

    // =========================
    // REMOVE ROW
    // =========================
    $('#append_akun').on('click','.btn-remove',function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
        refreshSku();
    });

    // =========================
    // ANTI DUPLIKAT SKU
    // =========================
    function refreshSku(){
        let selected = [];
        $('.sku').each(function(){
            if ($(this).val()) selected.push($(this).val());
        });

        $('.sku').each(function(){
            let current = $(this).val();
            $(this).find('option').each(function(){
                let val = $(this).val();
                $(this).prop(
                    'disabled',
                    selected.includes(val) && val !== current
                );
            });
        });
    }

    refreshSku();

});
</script>
@endsection
